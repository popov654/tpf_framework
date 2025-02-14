<?php

namespace Tpf\Service;

use Tpf\Database\Repository;
use Tpf\Model\AbstractEntity;
use Tpf\Model\Category;
use Tpf\Model\Comment;

class ImportExport
{
    public static function exportEntities(string $type, array $entities): array
    {
        $comments = [];

        $categoriesIds = [];

        $result = [];
        foreach ($entities as $entity) {
            $result[] = $entity->getFields();
            if (in_array('categories', get_object_vars($entity))) {
                $categoriesIds = array_merge($categoriesIds, $entity->categories);
                $comments = array_merge($comments, $entity->getComments());
            }
        }

        $categories = [];

        if (!empty($categoriesIds)) {
            $categoriesIds = array_unique($categoriesIds);
            $categories = (new Repository(Category::class))->where(['`id` IN (' . implode(',', $categoriesIds) . ')'])->fetch();

            $categories = array_map(function ($category) {
                return $category->getFields();
            }, $categories);
        }

        $comments = array_map(function($comment) {
            return $comment->getFields();
        }, $comments);

        return ['data' => [$type => $result], 'categories' => $categories, 'comments' => $comments];
    }

    public static function importData(array $data): array
    {
        $categoriesRepository = new Repository(Category::class);

        $map = [];

        $importedEntities = ['data' => [], 'categories' => [], 'comments' => []];

        if (isset($data['categories'])) {
            $categoriesRepository = new Repository(Category::class);
            foreach ($data['categories'] as $categoryData) {
                if ($categoriesRepository->where(['CAST(`path` as CHAR) = \'["' . implode('", "', $categoryData['path']) . '"]\''])->count() > 0) {
                    continue;
                }
                $category = new Category();
                Category::fillFromArray($category, $categoryData);
                $category->save();

                $importedEntities['categories'] = ['id' => $category->id, 'type' => $category->type, 'path' => $categoryData['path']];
            }
        }
        if (isset($data['data'])) {
            foreach ($data['data'] as $type => $entities) {
                $className = getFullClassNameByType($type);
                $repository = new Repository($className);
                foreach ($entities as $entityData) {
                    $categoryData = null;
                    if (!empty($entityData['categories'])) {
                        $categoryData = array_find(function ($category) use ($entityData) {
                            return $category['id'] == end($entityData['categories']);
                        }, $data['categories']);
                    }

                    $path = $categoryData ? $categoryData['path'] : '';

                    $items = $repository->whereEq(['name' => $entityData['name']])->fetch();
                    $items = array_filter_values(function ($entity) use ($entityData, $path) {
                        if (empty($entityData['categories']) && empty($entity->categories)) return true;
                        if (!empty($entityData['categories']) && !empty($entity->categories)) {
                            $category = Category::load(end($entity->categories));
                            return $category->path == $path;
                        }
                        return false;
                    }, $items);
                    if (!empty($items)) {
                        continue;
                    }

                    $entity = new $className();
                    AbstractEntity::fillFromArray($entity, $entityData);
                    $entity->id = 0;
                    $entity->save();

                    $entityData['id'] = $entity->id;
                    $entityData['type'] = $type;
                    $entityData['path'] = $path;

                    $entityData = array_filter_keys($entityData, ['id', 'type', 'name', 'authorId', 'categories', 'path']);
                    $importedEntities['data'][] = $entityData;

                    $map[$type][$entityData['id']] = $entity->id;
                }
            }
        }
        if (isset($data['comments'])) {
            $commentsRepository = new Repository(Comment::class);
            foreach ($data['comments'] as $commentData) {
                if (!isset($map[$commentData['type'][$commentData['entityId']]])) continue;
                $commentData['entityId'] = $map[$commentData['type'][$commentData['entityId']]] ?? $commentData['entityId'];
                $comment = new Comment();
                Comment::fillFromArray($comment, $commentData);
                $comment->save();

                $commentData['id'] = $comment->id;
                $commentData = array_filter_keys($commentData, ['id', 'type', 'authorId', 'text']);

                $importedEntities['comments'] = $commentData;
            }
        }

        return $importedEntities;
    }
}