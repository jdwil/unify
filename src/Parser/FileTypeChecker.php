<?php

namespace JDWil\Unify\Parser;

class FileTypeChecker
{
    const PHP = 0;

    public function determineType($path)
    {
        $type = mime_content_type($path);

        switch ($type) {
            case 'text/x-php':
                return self::PHP;
        }
    }
}
