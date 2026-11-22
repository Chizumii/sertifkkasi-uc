<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Project ini hanya memakai unit test (folder tests/Unit) dan tidak ada
| test yang membaca atau menyimpan data ke database.
|
| Test model memakai TestCase Laravel karena model butuh helper tanggal
| Laravel (today(), cast tanggal) dan travelTo() untuk mengatur hari ini.
|
*/

pest()->extend(TestCase::class)->in('Unit/Models');
