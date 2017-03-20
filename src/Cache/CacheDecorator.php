<?php

namespace Isswp101\Persimmon\Cache;

use Isswp101\Persimmon\Contracts\Storable;
use Isswp101\Persimmon\Repository\IRepository;

class CacheDecorator
{
    private $repository;
    private $cacheRepository;
    private $allColumns = [];

    public function __construct(IRepository $repository, IRepository $cacheRepository)
    {
        $this->repository = $repository;
        $this->cacheRepository = $cacheRepository;
    }

    private function getHash(string $id, string $class): string
    {
        return $class . '@' . $id;
    }

    public function find(string $id, string $class, array $columns = []): ?Storable
    {
        $hash = $this->getHash($class, $id);
        $model = $this->cacheRepository->find($id, $class, $columns);
        if ($model) {
            if ($columns) {
                if (!array_diff($columns, array_keys($model->toArray()))) {
                    return $model;
                }
            } else {
                if ($this->allColumns[$hash] ?? false) {
                    return $model;
                }
            }
        }
        $model = $this->repository->find($id, $class, $columns);
        $this->cacheRepository->update($model);
        if (!$columns) {
            $this->allColumns[$hash] = true;
        }
        return $model;
    }
}