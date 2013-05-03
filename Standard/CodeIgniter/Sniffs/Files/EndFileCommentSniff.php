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
 * @category  Files
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 * @author    Thomas Ernest <thomas.ernest@baobaz.com>
 */
class CodeIgniter_Sniffs_Files_EndFileCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_OPEN_TAG);

    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We are only interested if this is the first open tag.
        if ($stackPtr !== 0) {
            if ($phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1)) !== false) {
                return;
            }
        }

        $fullFilename = $phpcsFile->getFilename();
        $filename = basename($fullFilename);
        $template = array(
            " End of file $filename ",
            " Location: ./{path-to-file}/$filename "
        );
        $templateMatch = array(
            " End of file ".preg_quote($filename, '|')." ",
            " Location: \..*/".preg_quote($filename, '|')." "
        );
        $commentTemplate = "/* End of file $filename */ /* Location: ./{path-to-file}/$filename */";

        $tokens = $phpcsFile->getTokens();
        $currentToken = count($tokens) - 1;
        $hasClosingFileComment = false;
        $isNotAWhitespaceOrAComment = false;
        $commentCount = count($template) - 1;
        while ($currentToken >= 0
            && ! $isNotAWhitespaceOrAComment
            && ! $hasClosingFileComment
        ) {
            $token = $tokens[$currentToken];
            $tokenCode = $token['code'];
            if (T_COMMENT === $tokenCode) {
                $commentString = $this->getCommentContent($token['content']);
                if (1 === preg_match('|'.$templateMatch[$commentCount].'|', $commentString)) {
                    $hasClosingFileComment = ($commentCount-- == 0);
                }
            } else if (T_WHITESPACE === $tokenCode) {
                // Whitespaces are allowed between the closing file comment,
                //other comments and end of file
            } else {
                $isNotAWhitespaceOrAComment = true;
            }
            $currentToken--;
        }

        if ( ! $hasClosingFileComment) {
            $error = 'Require an end of file comment block containing "' . $commentTemplate . '".';
            $phpcsFile->addError($error, count($tokens)-1);
        }
    }//end process()
    
    protected function getCommentContent ($comment)
    {
        if ($this->stringStartsWith($comment, '#')) {
            $comment = substr($comment, 1);
        } else if ($this->stringStartsWith($comment, '//')) {
            $comment = substr($comment, 2);
        } else if ($this->stringStartsWith($comment, '/*')) {
            $comment = substr($comment, 2, strlen($comment) - 2 - 2);
        }
        //$comment = trim($comment);
        return $comment;
    }
    
    /**
     * Binary safe string comparison between $needle and
     * the beginning of $haystack. Returns true if $haystack starts with
     * $needle, false otherwise.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The string to search for.
     *
     * @return bool true if $haystack starts with $needle, false otherwise.
     */
    protected function stringStartsWith ($haystack, $needle)
    {
        $startsWith = false;
        if (strlen($needle) <= strlen($haystack)) {
            $haystackBeginning = substr($haystack, 0, strlen($needle));
            if (0 === strcmp($haystackBeginning, $needle)) {
                $startsWith = true;
            }
        }
        return $startsWith;
    }//_stringStartsWith()

}//end class