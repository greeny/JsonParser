<?php declare(strict_types = 1);

require __DIR__ . '/../bootstrap.php';

test('0', 0);
test('1', 1);
test('1.1', 1.1);

test('-0', 0);
test('-0.1', -0.1);
test('-1', -1);
test('-1.1', -1.1);

test('1e2', 100);
test('2e2', 200);
test('2.1e2', 210);

test('-1e2', -100);
test('-2e2', -200);
test('-2.1e2', -210);

test('1E2', 100);

test('1e+2', 100);
test('1e-2', 0.01);
