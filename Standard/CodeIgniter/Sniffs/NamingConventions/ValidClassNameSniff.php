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
 */
class CodeIgniter_Sniffs_NamingConventions_ValidClassNameSniff implements PHP_CodeSniffer_Sniff
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
     * @param PHP_CodeSniffer_File $phpcsFile The current file being processed.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $className = $phpcsFile->findNext(T_STRING, $stackPtr);
        $name      = trim($tokens[$className]['content']);
        $errorData = array(ucfirst($tokens[$stackPtr]['content']));

        // Make sure the first letter is a capital.
        if (preg_match('|^[A-Z]|', $name) === 0) {
            $error = '%s name must begin with a capital letter';
            $phpcsFile->addError($error, $stackPtr, 'StartWithCapital', $errorData);
        }

        // Ensure it is not CamelCase
        if (preg_match('|[a-z][A-Z]|', $name) !== 0) {
            $varName = $matches[0];
            $error = '%s name must not be in camel caps format';
            $phpcsFile->addError($error, $stackPtr, 'StringCamelCaps', $errorData);
            
        }
    }//end process()

}//end class