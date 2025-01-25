<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Import Log facade

class FetchBooks extends Command
{
    protected $signature = 'app:fetch-books';
    protected $description = 'Fetch books from the Fake Books API and store them in the database.';
    
    public function handle()
    {
        try {
            $response = Http::timeout(10)->get('https://fakerapi.it/api/v1/books?_quantity=3'); // Set a timeout

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data'])) { // Check if 'data' key exists and is an array
                    foreach ($data['data'] as $bookData) {
                        Book::updateOrCreate(
                            ['isbn' => $bookData['isbn'] ?? null], // Handle potential missing ISBNs
                            [
                                'title' => $bookData['title'] ?? null,
                                'author' => $bookData['author'] ?? null,
                                'genre' => $bookData['genre'] ?? null,
                                'publisher' => $bookData['publisher'] ?? null,
                                'cover' => $bookData['image'] ?? null,
                                'description' => $bookData['description'] ?? null,
                                'published' => $bookData['published'] ?? null,
                            ]
                        );
                    }
                    $this->info('Books fetched and stored successfully.');
                    log::info('Books fetched and stored successfully.');
                } else {
                    Log::error('Invalid API response format: Missing or invalid "data" key.', $data);
                    $this->error('Invalid API response format.');
                }
            } else {
                Log::error('Failed to fetch books from the API.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->error('Failed to fetch books from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching books: ' . $e->getMessage());
            $this->error('An error occurred while fetching books.');
        }
    }
}