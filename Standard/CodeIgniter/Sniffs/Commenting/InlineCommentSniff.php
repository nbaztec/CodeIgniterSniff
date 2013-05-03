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
 * @category  Commenting
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 */
 class CodeIgniter_Sniffs_Commenting_InlineCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('PHP');

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_COMMENT,
                T_DOC_COMMENT,
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

        // If this is a function/class/interface doc block comment, skip it.
        // We are only interested in inline doc block comments, which are
        // not allowed.
        if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT) {
            $nextToken = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($stackPtr + 1),
                null,
                true
            );

            $ignore = array(
                       T_CLASS,
                       T_INTERFACE,
                       T_TRAIT,
                       T_FUNCTION,
                       T_PUBLIC,
                       T_PRIVATE,
                       T_PROTECTED,
                       T_FINAL,
                       T_STATIC,
                       T_ABSTRACT,
                       T_CONST,
                       T_OBJECT,
                       T_PROPERTY,
                      );

            if (in_array($tokens[$nextToken]['code'], $ignore) === true) {
                return;
            } else {
                if ($phpcsFile->tokenizerType === 'JS') {
                    // We allow block comments if a function is being assigned
                    // to a variable.
                    $ignore    = PHP_CodeSniffer_Tokens::$emptyTokens;
                    $ignore[]  = T_EQUAL;
                    $ignore[]  = T_STRING;
                    $ignore[]  = T_OBJECT_OPERATOR;
                    $nextToken = $phpcsFile->findNext($ignore, ($nextToken + 1), null, true);
                    if ($tokens[$nextToken]['code'] === T_FUNCTION) {
                        return;
                    }
                }

                $prevToken = $phpcsFile->findPrevious(
                    PHP_CodeSniffer_Tokens::$emptyTokens,
                    ($stackPtr - 1),
                    null,
                    true
                );

                if ($tokens[$prevToken]['code'] === T_OPEN_TAG) {
                    return;
                }
            }//end if
        }//end if

        if ($tokens[$stackPtr]['content']{0} === '#') {
            $error  = 'Perl-style comments are not allowed; use "// Comment" instead';
            $phpcsFile->addError($error, $stackPtr, 'WrongStyle');
        }

        // We don't want end of block comments. If the last comment is a closing
        // curly brace.
        $previousContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($tokens[$previousContent]['line'] === $tokens[$stackPtr]['line']) {
            if ($tokens[$previousContent]['code'] === T_CLOSE_CURLY_BRACKET) {
                return;
            }

            // Special case for JS files.
            if ($tokens[$previousContent]['code'] === T_COMMA
                || $tokens[$previousContent]['code'] === T_SEMICOLON
            ) {
                $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($previousContent - 1), null, true);
                if ($tokens[$lastContent]['code'] === T_CLOSE_CURLY_BRACKET) {
                    return;
                }
            }
        }

        $comment = rtrim($tokens[$stackPtr]['content']);

        // Only want inline comments.
        if (substr($comment, 0, 2) !== '//') {
            return;
        }

        $spaceCount = 0;
        for ($i = 2; $i < strlen($comment); $i++) {
            if ($comment[$i] !== ' ') {
                break;
            }

            $spaceCount++;
        }

        if ($spaceCount === 0) {
            $error = 'No space before comment text; expected "// %s" but found "%s"';
            $data  = array(
                      substr($comment, 2),
                      $comment,
                     );
            $phpcsFile->addError($error, $stackPtr, 'NoSpaceBefore', $data);
        }

        if ($spaceCount > 1) {
            $error = '%s spaces found before inline comment line; use block comment if you need indentation';
            $data  = array(
                      $spaceCount,
                      substr($comment, (2 + $spaceCount)),
                      $comment,
                     );
            $phpcsFile->addError($error, $stackPtr, 'SpacingBefore', $data);
        }

        // Must have a space before comment
        if ($stackPtr > 1) {
            $spaces = 1;
            if ($tokens[$stackPtr-1]['line'] === ($tokens[$stackPtr]['line'])) {
                $spaces = 0;
                if ($tokens[$stackPtr-1]['code'] === T_WHITESPACE) {
                    if ($tokens[$stackPtr-2]['line'] === $tokens[$stackPtr]['line'] &&  $tokens[$stackPtr-2]['code'] !== T_WHITESPACE) {
                        $spaces = strlen($tokens[$stackPtr-1]['content']);
                    } else {
                        $spaces = 1;
                    }
                }
            }

            if ($spaces !== 1) {
                $error = 'Single space is expected before the comment';
                $phpcsFile->addError($error, $stackPtr, 'SpacingBefore');
            }
        }
    }//end process()

}//end class
