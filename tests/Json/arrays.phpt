<?php declare(strict_types = 1);

require __DIR__ . '/../bootstrap.php';

test('[]', []);
test('[1,2,3]', [1, 2, 3]);
test('[ 1, 2, 3 ]', [1, 2, 3]);
test('[
	1,
	2,
	3
]', [1, 2, 3]);
test('["test", 1, true, false, null]', ['test', 1, TRUE, FALSE, NULL]);
