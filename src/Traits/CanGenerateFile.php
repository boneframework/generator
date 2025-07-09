<?php

declare(strict_types=1);

namespace Bone\Generator\Traits;

use Bone\Generator\Exception\GeneratorException;
use Exception;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use RuntimeException;
use function array_shift;
use function explode;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;

trait CanGenerateFile
{
    protected function writeFile(PhpFile $file, string $fileName): void
    {
        try {
            $printer = new PsrPrinter();
            $code = $printer->printFile($file);
            $result = file_put_contents($fileName, $code);

            if (!$result) {
                throw new GeneratorException(sprintf(GeneratorException::FILE_WRITE_ERROR, $fileName));
            }
        } catch (Exception $e) {
            throw new GeneratorException($e->getMessage(), 500, $e);
        }
    }

    protected function ensureNoFileExists(string $fileName): void
    {
        if ($this->fileExists($fileName)) {
            throw new GeneratorException(GeneratorException::FILE_EXISTS);
        }
    }

    protected function fileExists(string $fileName): bool
    {
        return file_exists($fileName);
    }

    protected function makeFolder(string $folderName): void
    {
        $parts = explode('/', $folderName);
        $checkFolder = '';

        while (count($parts) > 0) {
            $checkFolder .= array_shift($parts) . '/';
            if (!$this->fileExists($checkFolder)) {
                if (!mkdir($checkFolder) && !is_dir($checkFolder)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $folderName));
                }
            }
        }
    }

    protected function createPHPFile(): PhpFile
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        return $file;
    }
}
