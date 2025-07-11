<?php

namespace Bone\Generator\Traits;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use function ctype_upper;
use function str_split;
use function strtolower;

trait CanGenerateRoutes
{
    private ?Inflector $inflector = null;

    /**
     * URL for Index and Create endpoints
     */
    protected function getIndexRoute(string $entityClass): string
    {
        $urlSlug = $this->getUrlSlug($entityClass);

        return '/' . $urlSlug;
    }

    /**
     * URL for Read, Update, and Delete record endpoints
     */
    protected function getRecordRoute(string $entityClass): string
    {
        $urlSlug = $this->getUrlSlug($entityClass);

        return '/' . $urlSlug . '/{id}';
    }

    protected function getUrlSlug(string $entityClass): string
    {
        $pluralised = $this->toPlural($entityClass);

        return  $this->camelCaseToDash($pluralised);
    }

    private function camelCaseToDash(string $key): string
    {
        $converted = '';

        foreach (str_split($key) as $index => $letter) {
            if (ctype_upper($letter)) {
                $letter = strtolower($letter);
                $letter = $index < 1 ? $letter : '-' . $letter;
            }

            $converted .= $letter;
        }

        return $converted;
    }

    private function toPlural(string $key): string
    {
        if (!$this->inflector) {
            $this->inflector = InflectorFactory::create()->build();
        }

        return $this->inflector->pluralize($key);
    }
}
