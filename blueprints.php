<?php

/**
 * php-blueprints
 * Copyright (C) 2021  Nikita Podvirnyy

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * GitHub: https://github.com/KRypt0nn
 * VK:     https://vk.com/technomindlp
 */

namespace Blueprints;

class Blueprints
{
    /**
     * @param string $inputDir - directory from which we will process files
     * @param string $outputDir - directory where we will save processed files
     */
    public function __construct (protected string $inputDir, protected string $outputDir) {}

    /**
     * Process blueprints files
     */
    public function process (): self
    {
        $this->_processDir ();

        return $this;
    }

    /**
     * Shortcut for this code:
     * 
     * (new Blueprints(.., ..))->process();
     * 
     * Blueprints::processDir(.., ..);
     */
    public static function processDir (string $inputDir, string $outputDir): self
    {
        return (new self ($inputDir, $outputDir))->process ();
    }

    protected function _processDir (string $dir = ''): void
    {
        foreach (array_slice (scandir ($abs_path = $this->inputDir .'/'. $dir), 2) as $file)
            if (is_file ($abs_path .'/'. $file))
                $this->processFile ($dir .'/'. $file);

            else $this->_processDir ($dir .'/'. $file);
    }

    /**
     * Process some file
     * 
     * @param string $file - file relative path (inside inputDir)
     * 
     * @return bool - was this file blueprint or not
     */
    public function processFile (string $file): bool
    {
        $fileContent = file ($this->inputDir .'/'. $file);

        /**
         * Blueprint usage
         */
        if (($lines = sizeof ($fileContent)) > 0 && substr (ltrim ($fileContent[0]), 0, 9) == '@include(')
        {
            $blueprint = substr (trim ($fileContent[0]), 9, -1);

            if (!file_exists ($blueptint_file = $this->inputDir .'/'. $blueprint .'.php'))
                throw new \Exception ('Blueprint \''. $blueprint .'\' is not exists');
            
            $newContent = file_get_contents ($blueptint_file);
            $sections = [];
            $section = null;

            /**
             * Parsing sections html
             */
            for ($i = 1; $i < $lines; ++$i)
                if (substr (ltrim ($fileContent[$i]), 0, 9) == '@section(')
                {
                    $section = substr (trim ($fileContent[$i]), 9, -1);

                    if (!isset ($sections[$section]))
                        $sections[$section] = '';
                }
                
                elseif (substr ($fileContent[$i], 0, 4) == '@end')
                    $section = null;

                elseif ($section !== null)
                    $sections[$section] .= $fileContent[$i];

            /**
             * Processing sections' htmls
             */
            foreach ($sections as $name => $html)
            {
                # Finding section height (amount of tabs and spaces)
                $section_height = 0;
                $j = 0;

                while ($html[$j] != ' ' && $html[$j] != "\t")
                    ++$j;

                for ($j;; ++$j)
                    if ($html[$j] == ' ')
                        ++$section_height;

                    elseif ($html[$j] == "\t")
                        $section_height += 4;

                    else break;

                $lines = explode (PHP_EOL, $newContent);
                $newContentModified = '';

                /**
                 * Processing new file lines
                 */
                foreach ($lines as $line)
                    if (trim ($line) == '@section('. $name .')')
                    {
                        # Finding original section height
                        $section_definition_height = 0;

                        for ($j = 0;; ++$j)
                            if ($line[$j] == ' ')
                                ++$section_definition_height;

                            elseif ($line[$j] == "\t")
                                $section_definition_height += 4;

                            else break;

                        /**
                         * Updating section content and its height
                         */
                        $newContentModified .=
                            str_repeat (' ', $section_definition_height) . '<!-- @section('. $name .') -->'. PHP_EOL .
                            str_repeat (' ', $section_definition_height);

                        if ($section_height == $section_definition_height)
                            $newContentModified .= trim ($html);

                        elseif ($section_height < $section_definition_height)
                        {
                            $prefix = str_repeat (' ', $section_definition_height - $section_height);

                            $newContentModified .= trim (implode (PHP_EOL, array_map (fn ($line) => $prefix . $line, explode (PHP_EOL, $html))));
                        }

                        else $newContentModified .= trim (implode (PHP_EOL, array_map (fn ($line) => self::rid_line ($line, $section_height - $section_definition_height), explode (PHP_EOL, $html))));

                        $newContentModified .= PHP_EOL;
                    }

                    else $newContentModified .= $line . PHP_EOL;

                $newContent = $newContentModified;
            }

            file_put_contents ($this->outputDir .'/'. $file, $newContent);

            return true;
        }

        return false;
    }

    protected static function rid_line (string $line, int $height): string
    {
        if (strlen ($line) == 0)
            return $line;
        
        $i = 0;

        while ($height > 0)
        {
            if ($line[$i] == ' ')
                --$height;

            elseif ($line == "\t")
                $height -= 4;

            else break;

            ++$i;
        }

        return $height < 0 ?
            str_repeat (' ', -$height) . substr ($line, $i) :
            substr ($line, $i);
    }
}
