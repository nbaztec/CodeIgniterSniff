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

if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false) {
    $error = 'Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * CodeIgniter_Sniffs_NamingConventions_ValidFileNameSniff.
 *
 * Tests that the file name matchs the name of the class that it contains.
 *
 * @package   CodeIgniterSniff
 * @category  NamingConventions
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 */
class CodeIgniter_Sniffs_NamingConventions_ValidVariableNameSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    public $forceUnderscore = TRUE;

    /**
     * Processes class member variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps) === true) {
            return;
        }

        $memberName     = ltrim($tokens[$stackPtr]['content'], '$');
        $isPublic       = ($memberProps['scope'] === 'private' || $memberProps['scope'] === 'protected') ? false : true;
        $scope          = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];

        // If it's a private member, it must have an underscore on the front.
        if ($this->forceUnderscore && $isPublic === false && $memberName[0] !== '_') {
            $error = 'Private member variable "%s" must be prefixed with an underscore';
            $data  = array($memberName);
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
            return;
        }


        // If it's not a private member, it must not have an underscore on the front.
        if ($isPublic === true && $scopeSpecified === true && $memberName[0] === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data  = array(
                      ucfirst($scope),
                      $memberName,
                     );
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        $data  = array($memberName);

        // Member name must be lowercase
        if (preg_match('|[A-Z]|', $memberName) !== 0) {
            $error = 'Member name "%s" must be lowercase';
            $phpcsFile->addError($error, $stackPtr, 'LowerCase', $data);
            return;
        }
        
        // Member name must be short
        if (strlen($memberName) > 20) {
            $error = 'Member name "%s" is too long';
            $phpcsFile->addWarning($error, $stackPtr, 'TooLong', $data);
            return;
        }
        
        // Member name must not be single character
        if (strlen($memberName) === 1) {
            $error = 'Member name "%s" is too short';
            $phpcsFile->addWarning($error, $stackPtr, 'TooShort', $data);
            return;
        }

    }//end processMemberVar()

    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
    }//end processVariable()


    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
    }//end processVariableInString()

}//end class