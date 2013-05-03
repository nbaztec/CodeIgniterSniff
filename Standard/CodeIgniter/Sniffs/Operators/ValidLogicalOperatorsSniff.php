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
 * @category  Operators
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 */
class CodeIgniter_Sniffs_Operators_ValidLogicalOperatorsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_LOGICAL_AND,
                T_LOGICAL_OR,
                T_BOOLEAN_OR,
                T_BOOLEAN_NOT,
               );

    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $replacements = array(
                         'and' => '&&',
                         '||'  => 'OR',
                        );

        if ($tokens[$stackPtr]['code'] === T_BOOLEAN_NOT) {
            if($tokens[$stackPtr-1]['code'] !== T_WHITESPACE || $tokens[$stackPtr+1]['code'] !== T_WHITESPACE) {
                $error = 'Must have spaces surrounding "%s"';
                $data  = array($tokens[$stackPtr]['content']);
                $phpcsFile->addError($error, $stackPtr, 'BadSpaces', $data);
            }
        }
        
        $operator = strtolower($tokens[$stackPtr]['content']);
        if (strtoupper($tokens[$stackPtr]['content']) !== $tokens[$stackPtr]['content']) {
            $error = 'Logical operator "%s" must be in uppercase; use "%s" instead';
            $data  = array(
                      $operator,
                      strtoupper($operator),
                     );
            $phpcsFile->addError($error, $stackPtr, 'NotAllowed', $data);
        }
        
        if (isset($replacements[$operator]) === false) {
            return;
        }

        $error = 'Operator "%s" is prohibited; use "%s" instead';
        $data  = array(
                  $tokens[$stackPtr]['content'] === '||'? 'T_BOOLEAN_OR' : $tokens[$stackPtr]['content'], // PhpStorm has a bug where it doesn't raise error if "||" is present in the message
                  $replacements[$operator],
                 );
        $phpcsFile->addWarning($error, $stackPtr, 'NotAllowed', $data);

    }//end process()

}//end class