<?php
/**
 * This file is part of CodeIgniterSniff.
 *
 * CodeIgniterSniff is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CodeIgniterSniff is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CodeIgniterSniff.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		CodeIgniterSniff
 * @author		Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

/**
 * CodeIgniter_Sniffs_NamingConventions_ValidFileNameSniff.
 *
 * Tests that the file name matchs the name of the class that it contains.
 *
 * @package   CodeIgniterSniff
 * @category  NamingConventions
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 * @author    Thomas Ernest <thomas.ernest@baobaz.com>
 */
class CodeIgniter_Sniffs_NamingConventions_ValidFileNameSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_CLASS,
            T_INTERFACE,
        );
    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        // computes the expected filename based on the name of the class or interface that it contains.
        $decNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);
        $decName = $tokens[$decNamePtr]['content'];

        // Check if the class name is prefixed
        if (preg_match('/[A-Z]{2}_(.*)/', $decName, $matches) === 1) {
            $decName = $matches[1];
        }

        $expectedFileName = ucfirst(strtolower($decName));
        // extracts filename without extension from its path.
        $fullPath = $phpcsFile->getFilename();
        $fileNameAndExt = basename($fullPath);
        $fileName = substr($fileNameAndExt, 0, strrpos($fileNameAndExt, '.'));

        if ($expectedFileName !== $fileName) {
            $data = array(
                $fileName,
                strtolower($tokens[$stackPtr]['content']), // class or interface
                $decName,
                $expectedFileName
            );
            $phpcsFile->addError('Filename "%s" doesn\'t match the name of the %s that it contains "%s" in lower case. "%s" was expected.', 0, 'IncorrectFilename', $data);
        }
    }//end process()
}//end class