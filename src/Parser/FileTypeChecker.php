<?php
/**
 * Copyright (c) 2017 - 2017 JD Williams
 *
 * This file is part of Unify, a PHP testing framework built by JD Williams. Unify is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * Unify is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. You should have received a copy of the GNU Lesser General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You should have received a copy of the GNU General Public License along with Unify. If not, see <http://www.gnu.org/licenses/>.
 */

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
