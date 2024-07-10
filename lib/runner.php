<?php

namespace WP_Parser;

use WP_Parser\Factory\Hook_ as HookStrategy;
use WP_Parser\HooksMetadata;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\ProjectFactory;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\Function_;
use phpDocumentor\Reflection\Php\Method;
use phpDocumentor\Reflection\Php\Property;

/**
 * Fixes newline handling in parsed text.
 *
 * DocBlock lines, particularly for descriptions, generally adhere to a given character width. For sentences and
 * paragraphs that exceed that width, what is intended as a manual soft wrap (via line break) is used to ensure
 * on-screen/in-file legibility of that text. These line breaks are retained by phpDocumentor. However, consumers
 * of this parsed data may believe the line breaks to be intentional and may display the text as such.
 *
 * This function fixes text by merging consecutive lines of text into a single line. A special exception is made
 * for text appearing in `<code>` and `<pre>` tags, as newlines appearing in those tags are always intentional.
 *
 * @param string $text
 *
 * @return string
 */
function fix_newlines( $text ) {
	// Non-naturally occurring string to use as temporary replacement.
	$replacement_string = '{{{{{}}}}}';

	// Replace newline characters within 'code' and 'pre' tags with replacement string.
	$text = preg_replace_callback(
		'/(?<=<pre><code>)(.+)(?=<\/code><\/pre>)/s',
		function ( $matches ) use ( $replacement_string ) {
			return preg_replace( '/[\n\r]/', $replacement_string, $matches[1] );
		},
		$text
	);

	// Merge consecutive non-blank lines together by replacing the newlines with a space.
	$text = preg_replace(
		"/[\n\r](?!\s*[\n\r])/m",
		' ',
		$text
	);

	// Restore newline characters into code blocks.
	$text = str_replace( $replacement_string, "\n", $text );

	return $text;
}

/**
 * Extracts the namespace from a Fqsen
 *
 * @param \phpDocumentor\Reflection\Fqsen fqsen
 *
 * @return string
 */
function get_namespace( $fqsen ) {
	$parts = explode( '\\', ltrim( (string) $fqsen, '\\' ) );
	array_pop( $parts );

	return implode( '\\', $parts );
}

function export_docblock( Property|Method|Hook|File|Function_|Class_ $element ) : DocBlockData {
	$docblock = $element->getDocBlock();
	if ( ! $docblock ) {
		return new DocBlockData();
	}

	$tags = [];

	foreach ( $docblock->getTags() as $tag ) {
		$tag_data = array(
			'name' => $tag->getName(),
		);

		if ( method_exists( $tag, 'getDescription' ) ) {
			$description = $tag->getDescription();
			$tag_data['content'] = preg_replace( '/[\n\r]+/', ' ', $description ?? '' );
		}

		if ( method_exists( $tag, 'getType' ) ) {
			$tag_type = $tag->getType();

			if ( ! $tag_type instanceof \phpDocumentor\Reflection\Types\AggregatedType ) {
				$tag_data['types'] = array( (string) $tag_type );
			} else {
				foreach ( $tag_type->getIterator() as $index => $type ) {
					$tag_data['types'][] = (string) $type;
				}
			}
		}

		if ( method_exists( $tag, 'getLink' ) ) {
			$tag_data['link'] = $tag->getLink();
		}
		if ( method_exists( $tag, 'getVariableName' ) ) {
			$variable             = $tag->getVariableName();
			$tag_data['variable'] = $variable ? '$' . $variable : '';
		}
		if ( method_exists( $tag, 'getReference' ) ) {
			$tag_data['refers'] = (string) $tag->getReference();
		}
		if ( method_exists( $tag, 'getVersion' ) ) {
			// Version string.
			$version = $tag->getVersion();
			if ( ! empty( $version ) ) {
				$tag_data['content'] = $version;
			}
			// Description string.
			if ( method_exists( $tag, 'getDescription' ) ) {
				$description = preg_replace( '/[\n\r]+/', ' ', $tag->getDescription() );
				if ( ! empty( $description ) ) {
					$tag_data['description'] = $description;
				}
			}
		}

		$tags[] = new DocBlockTagData(
			$tag_data['name'],
			$tag_data['content'] ?? null,
			$tag_data['types'] ?? null,
			$tag_data['link'] ?? null,
			$tag_data['variable'] ?? null,
			$tag_data['refers'] ?? null,
			$tag_data['description'] ?? null,
		);
	}

	return new DocBlockData(
		preg_replace( '/[\n\r]+/', ' ', $docblock->getSummary() ),
		fix_newlines( $docblock->getDescription() ),
		new DocBlockTagDataList( ...$tags ),
	);
}

/**
 * @param \phpDocumentor\Reflection\Php\Argument[] $arguments
 *
 * @return array<int, ArgumentData>
 */
function export_arguments( array $arguments ) : array {
	$output = array();

	foreach ( $arguments as $argument ) {
		$output[] = new ArgumentData(
			'$' . $argument->getName(),
			$argument->getDefault(),
			(string) $argument->getType(),
		);
	}

	return $output;
}

/**
 * @param \phpDocumentor\Reflection\Php\Property[] $properties
 *
 * @return array<int, PropertyData>
 */
function export_properties( array $properties ) : array {
	$out = array();

	foreach ( $properties as $property ) {
		$out[] = new PropertyData(
			'$' . $property->getName(),
			$property->getLocation()->getLineNumber(),
			$property->getEndLocation()->getLineNumber(),
			$property->getDefault(),
			$property->isStatic(),
			(string) $property->getVisibility(),
			export_docblock( $property ),
		);
	}

	return $out;
}

/**
 * @param \phpDocumentor\Reflection\Php\Method[] $methods
 *
 * @return array<int, MethodData>
 */
function export_methods( array $methods ) : array {
	$output = array();

	foreach ( $methods as $method ) {

		$namespace = get_namespace( $method->getFqsen() );

		$output[] = new MethodData(
			$method->getName(),
			$namespace ? $namespace : '',
			$method->getLocation()->getLineNumber(),
			$method->getEndLocation()->getLineNumber(),
			$method->isFinal(),
			$method->isAbstract(),
			$method->isStatic(),
			(string) $method->getVisibility(),
			export_arguments( $method->getArguments() ),
			export_docblock( $method ),
		);
	}

	return $output;
}

/**
 * @param HooksMetadata $hooks_metadata
 *
 * @return array<int, HookData>
 */
function export_hooks( HooksMetadata $hooks_metadata ) : array {
	$out = array();

	/** @var Hook $hook */
	foreach ( $hooks_metadata as $hook ) {
		$out[] = new HookData(
			$hook->getName(),
			$hook->getLocation()->getLineNumber(),
			$hook->getEndLocation()->getLineNumber(),
			$hook->getType(),
			$hook->getArgs(),
			export_docblock( $hook ),
		);
	}

	return $out;
}

/**
 * @param string $directory
 *
 * @throws \InvalidArgumentException If the directory does not exist.
 * @throws \RuntimeException If the directory contains a directory that cannot be recursed into.
 *
 * @return array<int, string>
 */
function get_wp_files( $directory ) {

	if ( ! is_dir( $directory ) ) {
		throw new \InvalidArgumentException(
			sprintf( 'Directory [%s] does not exist.', $directory )
		);
	}

	$iterable_files = new \RecursiveIteratorIterator(
		new \RecursiveDirectoryIterator( $directory )
	);

	$files = array();

	try {
		foreach ( $iterable_files as $file ) {
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
			}
		}
	} catch ( \UnexpectedValueException $exc ) {
		throw new \RuntimeException(
			sprintf( 'Directory [%s] contains a directory that cannot be recursed into', $directory )
		);
	}

	return $files;
}

/**
 * @param array<int, string> $files
 * @param string             $root
 *
 * @return array
 */
function parse_files( $files, $root ): array {
	$project_files = array();

	foreach ( $files as $file ) {
		$project_files[] = new LocalFile( $file );
	}

	$project_factory = ProjectFactory::createInstance();

	$hook_strategy = new HookStrategy( DocBlockFactory::createInstance() );

	$project_factory->addStrategy( $hook_strategy );

	$project = $project_factory->create( 'WP_Parser', $project_files );

	$output = array();

	/** @var \phpDocumentor\Reflection\Php\File $file */
	foreach ( $project->getFiles() as $file ) {

		$out = array(
			'file' => export_docblock( $file ),
			'path' => ltrim( substr( $file->getPath(), strlen( $root ) ), DIRECTORY_SEPARATOR ),
			'root' => $root,
		);

		foreach ( $file->getIncludes() as $include ) {
			$out['includes'][] = array(
				'name' => $include->getName(),
				'line' => $include->getLocation()->getLineNumber(),
				'type' => $include->getType(),
			);
		}

		foreach ( $file->getConstants() as $constant ) {
			$out['constants'][] = array(
				'name'  => $constant->getName(),
				'line'  => $constant->getLocation()->getLineNumber(),
				'value' => $constant->getValue(),
			);
		}

		if ( array_key_exists( 'hooks', $file->getMetadata() ) ) {
			$out['hooks'] = export_hooks( $file->getMetadata()['hooks'] );
		}

		foreach ( $file->getFunctions() as $function ) {

			$namespace = get_namespace( $function->getFqsen() );

			$func = array(
				'name'      => $function->getName(),
				'namespace' => $namespace ? $namespace : 'global',
				'line'      => $function->getLocation()->getLineNumber(),
				'end_line'  => $function->getEndLocation()->getLineNumber(),
				'arguments' => export_arguments( $function->getArguments() ),
				'doc'       => export_docblock( $function ),
				'hooks'     => array(),
			);

			$out['functions'][] = $func;
		}

		foreach ( $file->getClasses() as $class ) {

			$parts = explode( '\\', ltrim( $class->getFqsen(), '\\' ) );
			array_pop( $parts );

			$namespace = implode( '\\', $parts );

			$class_data = array(
				'name'       => $class->getName(),
				'namespace'  => $namespace ? $namespace : 'global',
				'line'       => $class->getLocation()->getLineNumber(),
				'end_line'   => $class->getEndLocation()->getLineNumber(),
				'final'      => $class->isFinal(),
				'abstract'   => $class->isAbstract(),
				'extends'    => $class->getParent() !== null ? (string) $class->getParent() : '',
				'implements' => $class->getInterfaces(),
				'properties' => export_properties( $class->getProperties() ),
				'methods'    => export_methods( $class->getMethods() ),
				'doc'        => export_docblock( $class ),
			);

			$out['classes'][] = $class_data;
		}

		$output[] = $out;
	}

	return $output;
}
