<?php

namespace Database\Seeders;

use App\Services\BookInventoryService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = \App\Models\Category::all();

        $sampleBooks = [
            ['title' => 'Madol Doova', 'author' => 'Martin Wickramasinghe', 'isbn' => '9789551234567', 'category' => 'Fiction', 'language' => 'Sinhala', 'quantity' => 10],
            ['title' => 'A Brief History of Time', 'author' => 'Stephen Hawking', 'isbn' => '9780553380163', 'category' => 'Science & Technology', 'language' => 'English', 'quantity' => 5],
            ['title' => 'Mahavamsa', 'author' => 'Mahanama Thera', 'isbn' => '9789559523451', 'category' => 'History & Geography', 'language' => 'Pali/Sinhala/English', 'quantity' => 3],
            ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isbn' => '9780061120084', 'category' => 'Fiction', 'language' => 'English', 'quantity' => 8],
            ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '9780451524935', 'category' => 'Fiction', 'language' => 'English', 'quantity' => 6],
            ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isbn' => '9780743273565', 'category' => 'Literature', 'language' => 'English', 'quantity' => 7],
            ['title' => 'Sapiens', 'author' => 'Yuval Noah Harari', 'isbn' => '9780062316097', 'category' => 'History & Geography', 'language' => 'English', 'quantity' => 4],
            ['title' => 'Atomic Habits', 'author' => 'James Clear', 'isbn' => '9780735211292', 'category' => 'Self-Help & Psychology', 'language' => 'English', 'quantity' => 5],
            ['title' => 'The Origin of Species', 'author' => 'Charles Darwin', 'isbn' => '9780140436174', 'category' => 'Science & Technology', 'language' => 'English', 'quantity' => 3],
            ['title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'isbn' => '9780141439518', 'category' => 'Literature', 'language' => 'English', 'quantity' => 9],
            ['title' => 'The Selfish Gene', 'author' => 'Richard Dawkins', 'isbn' => '9780192860926', 'category' => 'Science & Technology', 'language' => 'English', 'quantity' => 2],
            ['title' => 'Educated', 'author' => 'Tara Westover', 'isbn' => '9780399590504', 'category' => 'Biography & Memoir', 'language' => 'English', 'quantity' => 6],
            ['title' => 'The Lean Startup', 'author' => 'Eric Ries', 'isbn' => '9780670921602', 'category' => 'Business & Economics', 'language' => 'English', 'quantity' => 4],
            ['title' => 'Python Programming', 'author' => 'Mark Lutz', 'isbn' => '9781491957592', 'category' => 'Technology & Computer Science', 'language' => 'English', 'quantity' => 5],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9780132350884', 'category' => 'Technology & Computer Science', 'language' => 'English', 'quantity' => 3],
            ['title' => 'The Art of War', 'author' => 'Sun Tzu', 'isbn' => '9780143039990', 'category' => 'Religion & Philosophy', 'language' => 'English', 'quantity' => 4],
            ['title' => 'Thinking, Fast and Slow', 'author' => 'Daniel Kahneman', 'isbn' => '9780374275631', 'category' => 'Self-Help & Psychology', 'language' => 'English', 'quantity' => 5],
            ['title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'isbn' => '9780547928227', 'category' => 'Fiction', 'language' => 'English', 'quantity' => 8],
            ['title' => 'Java Programming', 'author' => 'Herbert Schildt', 'isbn' => '9780073352519', 'category' => 'Technology & Computer Science', 'language' => 'English', 'quantity' => 4],
            ['title' => 'The Intelligent Investor', 'author' => 'Benjamin Graham', 'isbn' => '9780061715525', 'category' => 'Business & Economics', 'language' => 'English', 'quantity' => 3],
        ];

        $inventoryService = new BookInventoryService();

        foreach ($sampleBooks as $bookData) {
            $category = $categories->where('name', $bookData['category'])->first();
            if ($category) {
                $book = \App\Models\Book::create([
                    'title' => $bookData['title'],
                    'author' => $bookData['author'],
                    'isbn' => $bookData['isbn'],
                    'category_id' => $category->id,
                    'language' => $bookData['language'],
                    'total_quantity' => $bookData['quantity'],
                    'available_quantity' => $bookData['quantity'],
                    'location_rack' => 'R-' . str_pad(rand(1, 50), 2, '0', STR_PAD_LEFT)
                ]);

                $inventoryService->provisionCopies($book, (int) $bookData['quantity']);
            }
        }
    }
}
