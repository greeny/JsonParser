<?php declare(strict_types = 1);

require __DIR__ . '/../bootstrap.php';

testInvalid('{');
testInvalid(']');
testInvalid('a');
testInvalid('01');
testInvalid('1.1.1');
testInvalid('"""');
testInvalid('"\x123"');
testInvalid('{}{}');
testInvalid('"a": "b"');
testInvalid('{"a": "b"');
testInvalid('"a": "b"}');
testInvalid('{"a" "b"}');
testInvalid('["a": "b"]');
