<?php

namespace Tpf\Tests;

use App\Model\Blog\Post;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Tpf\Model\Category;
use Tpf\Model\Comment;
use Tpf\Service\ImportExport;


class ImportTest extends BasicTest
{

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function testImportFromFile()
    {
        global $dbal;
        dbConnect();

        $data = Utils::seedBlogPosts();

        try {
            $contents = file_get_contents(PATH . '/vendor/' . VENDOR_PATH . '/Tests/data/blog_post.json');
            $importData = json_decode($contents, true);
            $results = ImportExport::importData($importData);
            self::assertEquals(1, count($results['data']));
            foreach ($results['data'] as $item) {
                if ($item['type'] == 'blog_post') {
                    $post = new Post();
                    Post::fillFromArray($post, $item);
                    $data['posts'][] = $post;
                }
            }
            foreach ($results['categories'] as $item) {
                $category = new Category();
                Category::fillFromArray($category, $item);
                $data['categories'][] = $category;
            }
            foreach ($results['comments'] as $item) {
                $comment = new Comment();
                Comment::fillFromArray($comment, $item);
                $data['comments'][] = $comment;
            }
        } finally {
            Utils::cleanupPostData($data);
        }
    }

}