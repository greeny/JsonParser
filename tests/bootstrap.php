<?php declare(strict_types = 1);

use greeny\Json\Parser;
use greeny\Json\ParserException;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';

Environment::setup();

function test(string $json, $value): void
{
	Assert::equal($value, Parser::parse($json));
}

function testInvalid(string $json): void
{
	Assert::exception(function () use ($json) {
		Parser::parse($json);
	}, ParserException::class);
}
