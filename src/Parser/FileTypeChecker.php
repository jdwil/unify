<?php

namespace JDWil\Unify\Parser;

class FileTypeChecker
{
    const PHP = 0;
    const MARKDOWN = 1;
    const REDCLOTH = 2;
    const RDOC = 3;
    const ORG = 4;
    const CREOLE = 5;
    const MEDIAWIKI = 6;
    const SPHINX = 7;
    const ASCIIDOC = 8;
    const POD = 9;

    public function determineType($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'php':
                return self::PHP;

            case 'md':
            case 'markdown':
            case 'mdown':
            case 'mkdn':
                return self::MARKDOWN;

            case 'textile':
                return self::REDCLOTH;

            case 'rdoc':
                return self::RDOC;

            case 'org':
                return self::ORG;

            case 'creole':
                return self::CREOLE;

            case 'mediawiki':
            case 'wiki':
                return self::MEDIAWIKI;

            case 'rst':
                return self::SPHINX;

            case 'asciidoc':
            case 'adoc':
            case 'asc':
                return self::ASCIIDOC;

            case 'pdo':
                return self::POD;
        }
    }
}
