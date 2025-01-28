<?php
namespace Tests\Feature\Http\Controllers;
use Tests\TestCase;
use App\Console\Commands\FetchBooks;

class BookTest extends TestCase
{
    public function test_fetch_books_command()
    {
        $this->artisan(FetchBooks::class)
            ->assertExitCode(0);
    }
}