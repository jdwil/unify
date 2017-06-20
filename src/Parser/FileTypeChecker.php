<?php

namespace JDWil\Unify\Parser;

class FileTypeChecker
{
    const PHP = 0;
    const MARKDOWN = 1;

    public function determineType($path)
    {
        $type = mime_content_type($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        switch ($type) {
            case 'text/x-php':
                return self::PHP;
            case 'text/plain':
                switch ($extension) {
                    case 'md':
                        return self::MARKDOWN;
                }
        }
    }
}
