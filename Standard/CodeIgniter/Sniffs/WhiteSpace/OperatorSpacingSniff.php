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
 * @category  WhiteSpace
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 */
class CodeIgniter_Sniffs_WhiteSpace_OperatorSpacingSniff implements PHP_CodeSniffer_Sniff
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
        $comparison = PHP_CodeSniffer_Tokens::$comparisonTokens;
        $operators  = PHP_CodeSniffer_Tokens::$operators;
        $assignment = PHP_CodeSniffer_Tokens::$assignmentTokens;

        return array_unique(array_merge($comparison, $operators, $assignment));

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being checked.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Skip default values in function declarations.
        if ($tokens[$stackPtr]['code'] === T_EQUAL
            || $tokens[$stackPtr]['code'] === T_MINUS
        ) {
            if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
                $parenthesis = array_keys($tokens[$stackPtr]['nested_parenthesis']);
                $bracket     = array_pop($parenthesis);
                if (isset($tokens[$bracket]['parenthesis_owner']) === true) {
                    $function = $tokens[$bracket]['parenthesis_owner'];
                    if ($tokens[$function]['code'] === T_FUNCTION
                        || $tokens[$function]['code'] === T_CLOSURE
                    ) {
                        return;
                    }
                }
            }
        }

        if ($tokens[$stackPtr]['code'] === T_EQUAL) {
            // Skip for '=&' case.
            if (isset($tokens[($stackPtr + 1)]) === true && $tokens[($stackPtr + 1)]['code'] === T_BITWISE_AND) {
                return;
            }
        }

        if ($tokens[$stackPtr]['code'] === T_BITWISE_AND) {
            // If it's not a reference, then we expect one space either side of the
            // bitwise operator.
            if ($phpcsFile->isReference($stackPtr) === true) {
                return;
            }

            // Check there is one space before the & operator.
            if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
                $error = 'Expected 1 space before "&" operator; 0 found';
                $phpcsFile->addError($error, $stackPtr, 'NoSpaceBeforeAmp');
            } else {
                if (strlen($tokens[($stackPtr - 1)]['content']) !== 1) {
                    $found = strlen($tokens[($stackPtr - 1)]['content']);
                    $error = 'Expected 1 space before "&" operator; %s found';
                    $data  = array($found);
                    $phpcsFile->addError($error, $stackPtr, 'SpacingBeforeAmp', $data);
                }
            }

            // Check there is one space after the & operator.
            if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
                $error = 'Expected 1 space after "&" operator; 0 found';
                $phpcsFile->addError($error, $stackPtr, 'NoSpaceAfterAmp');
            } else {
                if (strlen($tokens[($stackPtr + 1)]['content']) !== 1) {
                    $found = strlen($tokens[($stackPtr + 1)]['content']);
                    $error = 'Expected 1 space after "&" operator; %s found';
                    $data  = array($found);
                    $phpcsFile->addError($error, $stackPtr, 'SpacingAfterAmp', $data);
                }
            }

            return;
        }//end if

        if ($tokens[$stackPtr]['code'] === T_MINUS) {
            // Check minus spacing, but make sure we aren't just assigning
            // a minus value or returning one.
            $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            if ($tokens[$prev]['code'] === T_RETURN) {
                // Just returning a negative value; eg. (return -1).
                return;
            }

            if (in_array($tokens[$prev]['code'], PHP_CodeSniffer_Tokens::$operators) === true) {
                // Just trying to operate on a negative value; eg. ($var * -1).
                return;
            }

            if (in_array($tokens[$prev]['code'], PHP_CodeSniffer_Tokens::$comparisonTokens) === true) {
                // Just trying to compare a negative value; eg. ($var === -1).
                return;
            }

            if (in_array($tokens[$prev]['code'], PHP_CodeSniffer_Tokens::$assignmentTokens) === true) {
                // Just trying to assign a negative value; eg. ($var = -1).
                return;
            }

            // A list of tokens that indicate that the token is not
            // part of an arithmetic operation.
            $invalidTokens = array(
                              T_COMMA,
                              T_OPEN_PARENTHESIS,
                              T_OPEN_SQUARE_BRACKET,
                              T_DOUBLE_ARROW,
                              T_COLON,
                              T_INLINE_THEN,
                              T_INLINE_ELSE,
                              T_CASE,
                             );

            if (in_array($tokens[$prev]['code'], $invalidTokens) === true) {
                // Just trying to use a negative value; eg. myFunction($var, -2).
                return;
            }
        }//end if

        $operator = $tokens[$stackPtr]['content'];

        if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
            $error = "Expected 1 space before \"$operator\"; 0 found";
            $phpcsFile->addError($error, $stackPtr, 'NoSpaceBefore');
        } else if (strlen($tokens[($stackPtr - 1)]['content']) !== 1) {
            // Don't throw an error for assignments, because other standards allow
            // multiple spaces there to align multiple assignments.
            if (in_array($tokens[$stackPtr]['code'], PHP_CodeSniffer_Tokens::$assignmentTokens) === false) {
                $found = strlen($tokens[($stackPtr - 1)]['content']);
                $error = 'Expected 1 space before "%s"; %s found';
                $data  = array(
                          $operator,
                          $found,
                         );
                $phpcsFile->addError($error, $stackPtr, 'SpacingBefore', $data);
            }
        }

        if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
            $error = "Expected 1 space after \"$operator\"; 0 found";
            $phpcsFile->addError($error, $stackPtr, 'NoSpaceAfter');
        } else if (strlen($tokens[($stackPtr + 1)]['content']) !== 1) {
            $found = strlen($tokens[($stackPtr + 1)]['content']);
            $error = 'Expected 1 space after "%s"; %s found';
            $data  = array(
                      $operator,
                      $found,
                     );
            $phpcsFile->addError($error, $stackPtr, 'SpacingAfter', $data);
        }

    }//end process()

}//end class