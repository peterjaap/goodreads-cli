<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use App\Clients\Goodreads;
use kbATeam\MarkdownTable\Table;
use kbATeam\MarkdownTable\Column;

class Parse extends Command
{
    protected $signature = 'parse {filename}';

    protected $description = 'Parse CSV to create Markdown table';

    public function handle() : void
    {
        $client = new Goodreads();
        $client->setApiKey(env('GOODREADS_API_KEY'));

        $books = \Elgentos\Parser::readFile($this->argument('filename'));

        usort($books, function ($item1, $item2) {
            return $item1['author'] <=> $item2['author'];
        });

        $tableData = [];

        /** @var array $book */
        foreach ($books as $book) {
            $this->line(sprintf('Quering Goodreads for %s - %s', $book['title'], $book['author']));
            $result = $client->getBookByTitle($book['title'], $book['author']);
            if (!isset($result['book']['id'])) {
                continue;
            }
            $book['isbn'] = is_array($result['book']['isbn']) ? '' : $result['book']['isbn'];
            $book['small_image_url'] = $result['book']['small_image_url'];
            $book['average_rating'] = $result['book']['average_rating'];
            $book['original_publication_year'] = is_array($result['book']['work']['original_publication_year']) ? '' : $result['book']['work']['original_publication_year'];
            $book['image'] = isset($book['small_image_url']) ? sprintf('![%s](%s)', $book['title'], $book['small_image_url']) : '';
            $book['url'] = $result['book']['url'];
            if ($book['url']) {
                $book['title'] = sprintf('[%s](%s)', $book['title'], $book['url']);
            }
            $tableData[] = $book;
        }

        $table = new Table();
        $table->addColumn('image', new Column('#', Column::ALIGN_LEFT));
        $table->addColumn('author', new Column('Auteur', Column::ALIGN_LEFT));
        $table->addColumn('title', new Column('Titel', Column::ALIGN_LEFT));
        $table->addColumn('isbn', new Column('ISBN', Column::ALIGN_LEFT));
        $table->addColumn('original_publication_year', new Column('Publicatiejaar', Column::ALIGN_LEFT));
        $table->addColumn('average_rating', new Column('Goodreads cijfer', Column::ALIGN_LEFT));

        foreach ($table->generate($tableData) as $row) {
            $this->line($row);
        }
    }
}
