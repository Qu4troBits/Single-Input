<?php

declare(strict_types=1);

$date1 = DateTimeImmutable::createFromFormat('Y-m', '2024/01');
var_dump($date1);

$date2 = DateTimeImmutable::createFromFormat('Y-m', '2024-13');
var_dump($date2);
