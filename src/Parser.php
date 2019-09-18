<?php declare(strict_types = 1);

namespace greeny\Json;

use Nette\Tokenizer\Exception;
use Nette\Tokenizer\Stream;
use Nette\Tokenizer\Token;
use Nette\Tokenizer\Tokenizer;
use Throwable;

class Parser
{

	private const T_WHITESPACE = 1;
	private const T_OBJECT_OPENING_BRACE = 2;
	private const T_OBJECT_CLOSING_BRACE = 3;
	private const T_ARRAY_OPENING_BRACE = 4;
	private const T_ARRAY_CLOSING_BRACE = 5;
	private const T_OBJECT_KEY_VALUE_DELIMITER = 6;
	private const T_NUMBER = 7;
	private const T_STRING = 8;
	private const T_CONSTANT = 9;
	private const T_ITEM_DELIMITER = 10;

	/** @var Stream */
	private static $stream;

	/**
	 * Parses given JSON string
	 *
	 * @param string $json
	 * @return mixed
	 * @throws ParserException
	 */
	public static function parse(string $json)
	{
		$tokenizer = new Tokenizer([
			self::T_WHITESPACE => '\\s+',
			self::T_OBJECT_OPENING_BRACE => '\\{',
			self::T_OBJECT_CLOSING_BRACE => '\\}',
			self::T_ARRAY_OPENING_BRACE => '\\[',
			self::T_ARRAY_CLOSING_BRACE => '\\]',
			self::T_OBJECT_KEY_VALUE_DELIMITER => ':',
			self::T_NUMBER => '-?(?:[1-9]\d*|0)(?:\.\d+)?(?:[eE][-+]?\d+)?',
			self::T_STRING => '"(?:[^"\\\\]*|\\\\["\\\\bfnrt\\/]|\\\\u[0-9a-f]{4})*"',
			self::T_CONSTANT => 'true|false|null',
			self::T_ITEM_DELIMITER => ',',
		]);

		try {
			self::$stream = $tokenizer->tokenize($json);
		} catch (Exception $e) {
			throw new ParserException($e->getMessage(), $e->getCode(), $e);
		}

		// get rid of whitespaces, so we don't have to handle them everywhere
		self::$stream->tokens = array_values(array_filter(self::$stream->tokens, function (Token $token) {
			return $token->type !== self::T_WHITESPACE;
		}));

		try {
			$value = self::parseValue();

			// check for any additional tokens
			$token = self::$stream->nextToken();
			if ($token !== NULL) {
				throw new ParserException('Unexpected \'' . $token->value . '\', expected end of file');
			}

			return $value;
		} catch (Throwable $e) {
			[$line, $column] = Tokenizer::getCoordinates($json, self::$stream->position);
			throw new ParserException('Error in JSON: ' . $e->getMessage() . ' on line ' . $line . ', column ' . $column, $e->getCode(), $e);
		}
	}

	/**
	 * Parses a value from current stream
	 *
	 * @return array|bool|float|int|string|NULL
	 * @throws ParserException
	 * @throws Exception
	 */
	private static function parseValue()
	{
		$token = self::getNextToken();

		switch ($token->type) {
			case self::T_CONSTANT:
				switch ($token->value) {
					case 'true':
						return TRUE;
					case 'false':
						return FALSE;
					case 'null':
						return NULL;
					default:
						throw new ParserException('Should not happen');
				}
			case self::T_NUMBER:
				return ($token->value == (int) $token->value) ? (int) $token->value : (float) $token->value; // == intentional
			case self::T_STRING:
				return self::normalizeString($token->value);
			case self::T_ARRAY_OPENING_BRACE:
				return self::parseArray();
			case self::T_OBJECT_OPENING_BRACE:
				return self::parseObject();
			default:
				throw new ParserException('Unexpected \'' . $token->value . '\'');
		}
	}

	/**
	 * Parses array of items
	 *
	 * @return array
	 * @throws ParserException
	 * @throws Exception
	 */
	private static function parseArray(): array
	{
		$array = [];

		// check for empty array
		if (self::$stream->isNext(self::T_ARRAY_CLOSING_BRACE)) {
			self::$stream->consumeToken();
			return $array;
		}

		do {
			$array[] = self::parseValue();

			$token = self::getNextToken();
			if ($token->type === self::T_ARRAY_CLOSING_BRACE) {
				break;
			}
			if ($token->type !== self::T_ITEM_DELIMITER) {
				throw new ParserException('Unexpected \'' . $token->value . '\', expected \',\' or \']\'');
			}
		} while (TRUE);
		return $array;
	}

	/**
	 * Parses object
	 *
	 * @return array
	 * @throws ParserException
	 * @throws Exception
	 */
	private static function parseObject(): array
	{
		$object = [];

		// check for empty object
		if (self::$stream->isNext(self::T_OBJECT_CLOSING_BRACE)) {
			self::$stream->consumeToken();
			return $object;
		}

		do {
			$token = self::getNextToken();
			if ($token->type !== self::T_STRING) {
				throw new ParserException('Unexpected \'' . $token->value . '\', expected object key');
			}
			$key = self::normalizeString($token->value);

			$token = self::getNextToken();
			if ($token->type !== self::T_OBJECT_KEY_VALUE_DELIMITER) {
				throw new ParserException('Unexpected \'' . $token->value . '\', expected \':\'');
			}

			$object[$key] = self::parseValue();

			$token = self::getNextToken();
			if ($token->type === self::T_OBJECT_CLOSING_BRACE) {
				break;
			}

			if ($token->type !== self::T_ITEM_DELIMITER) {
				throw new ParserException('Unexpected \'' . $token->value . '\', expected \',\' or \'}\'');
			}
		} while (TRUE);

		return $object;
	}

	/**
	 * Gets next available token, or throws an exception if end of file has been reached
	 *
	 * @return Token
	 * @throws Exception
	 * @throws ParserException
	 */
	private static function getNextToken(): Token
	{
		$token = self::$stream->consumeToken();
		if ($token === NULL) {
			throw new ParserException('Unexpected end of string, expected value');
		}
		return $token;
	}

	/**
	 * Normalizes string (removes backslashes, converts characters to unicode, etc.)
	 *
	 * @param string $string
	 * @return string
	 */
	private static function normalizeString(string $string): string
	{
		// remove quotation marks
		$string = substr($string, 1, -1);

		// replaced sequences
		$replace = [
			'\\/' => '/',
			'\\b' => chr(8),
			'\\f' => chr(12),
			'\\n' => "\n",
			'\\r' => "\r",
			'\\t' => "\t",
		];

		// handle escaped backslash correctly (e.g. \\n should become \n, not a backslash followed by newline)
		$parts = explode('\\\\', $string);

		$parts = str_replace(array_keys($replace), array_values($replace), $parts);
		foreach ($parts as $key => $value) {
			$parts[$key] = preg_replace_callback('/\\\\u\\d{4}/', function ($matches) {
				return mb_chr(hexdec(substr($matches[0], 2)), 'UTF-8');
			}, $value);
		}

		return implode('\\', $parts);
	}

}
