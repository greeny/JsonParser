<?php declare(strict_types = 1);

require __DIR__ . '/../bootstrap.php';

test('{}', []);
test('{"a": 1}', ['a' => 1]);
test('{"a": "b"}', ['a' => 'b']);
test('{"a": 1, "b": 2, "c": 3}', ['a' => 1, 'b' => 2, 'c' => 3]);
test('{"a": 1, "a": 2, "a": 3}', ['a' => 3]);
