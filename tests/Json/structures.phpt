<?php declare(strict_types = 1);

require __DIR__ . '/../bootstrap.php';

test('{
	"a": [
		1,
		true,
		3,
		"false"
	],
	"b": {
		"x": null,
		"y": false,
		"z": [
			{
				"test": "\n\n"
			}
		]
	},
	"c": {},
	"d": []
}', [
	'a' => [1, TRUE, 3, 'false'],
	'b' => [
		'x' => NULL,
		'y' => FALSE,
		'z' => [
			[
				'test' => "\n\n",
			],
		],
	],
	'c' => [],
	'd' => [],
]);
