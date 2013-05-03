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
 * @category  PHP
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 */
class CodeIgniter_Sniffs_PHP_LowerCaseKeywordSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_HALT_COMPILER,
                T_ABSTRACT,
                T_ARRAY,
                T_AS,
                T_BREAK,
                T_CALLABLE,
                T_CASE,
                T_CATCH,
                T_CLASS,
                T_CLONE,
                T_CONST,
                T_CONTINUE,
                T_DECLARE,
                T_DEFAULT,
                T_DO,
                T_ECHO,
                T_ELSE,
                T_ELSEIF,
                T_EMPTY,
                T_ENDDECLARE,
                T_ENDFOR,
                T_ENDFOREACH,
                T_ENDIF,
                T_ENDSWITCH,
                T_ENDWHILE,
                T_EVAL,
                T_EXIT,
                T_EXTENDS,
                T_FINAL,
                T_FINALLY,
                T_FOR,
                T_FOREACH,
                T_FUNCTION,
                T_GLOBAL,
                T_GOTO,
                T_IF,
                T_IMPLEMENTS,
                T_INCLUDE,
                T_INCLUDE_ONCE,
                T_INSTANCEOF,
                T_INSTEADOF,
                T_INTERFACE,
                T_ISSET,
                T_LIST,
                // T_LOGICAL_AND,
                // T_LOGICAL_OR,
                // T_LOGICAL_XOR,
                T_NAMESPACE,
                T_NEW,
                T_PRINT,
                T_PRIVATE,
                T_PROTECTED,
                T_PUBLIC,
                T_REQUIRE,
                T_REQUIRE_ONCE,
                T_RETURN,
                T_STATIC,
                T_SWITCH,
                T_THROW,
                T_TRAIT,
                T_TRY,
                T_UNSET,
                T_USE,
                T_VAR,
                T_WHILE,
               );

    }//end register()

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens  = $phpcsFile->getTokens();
        $keyword = $tokens[$stackPtr]['content'];
        if (strtolower($keyword) !== $keyword) {
            $error = 'PHP keywords must be lowercase; expected "%s" but found "%s"';
            $data  = array(
                      strtolower($keyword),
                      $keyword,
                     );
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }
    }//end process()

}//end class
