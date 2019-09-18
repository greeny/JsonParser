<?php declare(strict_types = 1);

require __DIR__ . '/../bootstrap.php';

test('"a"', 'a');
test('"a\\\\b"', 'a\\b');
test('"a\\/b"', 'a/b');
test('"a\\bb"', "a\x08b");
test('"a\\fb"', "a\x0cb");
test('"a\\nb"', "a\nb");
test('"a\\rb"', "a\rb");
test('"a\\tb"', "a\tb");
test('"a\\u0012b"', "a\u{12}b");
test('"a\\u1234b"', "a\u{1234}b");
