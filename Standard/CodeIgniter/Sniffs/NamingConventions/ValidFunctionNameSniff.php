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

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found');
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
class CodeIgniter_Sniffs_NamingConventions_ValidFunctionNameSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{
    public $forceUnderscore = FALSE;

    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $magicMethods = array(
                               'construct',
                               'destruct',
                               'call',
                               'callstatic',
                               'get',
                               'set',
                               'isset',
                               'unset',
                               'sleep',
                               'wakeup',
                               'tostring',
                               'set_state',
                               'clone',
                               'invoke',
                               'call',
                              );

    /**
     * A list of all PHP magic functions.
     *
     * @var array
     */
    protected $magicFunctions = array('autoload');

    /**
     * Constructs a PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff.
     */
    public function __construct()
    {
        parent::__construct(array(T_CLASS, T_INTERFACE, T_TRAIT), array(T_FUNCTION), true);

    }//end __construct()

    /**
     * Processes the tokens within the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     * @param int                  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = array($className.'::'.$methodName);

        // Is this a magic method. i.e., is prefixed with "__" ?
        if (preg_match('|^__|', $methodName) !== 0) {
            $magicPart = strtolower(substr($methodName, 2));
            if (in_array($magicPart, $this->magicMethods) === false) {
                 $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                 $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            }

            return;
        }

        // PHP4 constructors are allowed to break our rules.
        if ($methodName === $className) {
            return;
        }

        // PHP4 destructors are allowed to break our rules.
        if ($methodName === '_'.$className) {
            return;
        }

        $methodProps    = $phpcsFile->getMethodProperties($stackPtr);
        $isPublic       = ($methodProps['scope'] === 'private' || $methodProps['scope'] === 'protected') ? false : true;
        $scope          = $methodProps['scope'];
        $scopeSpecified = $methodProps['scope_specified'];

        // If it's a private method, it must have an underscore on the front.
        if ($this->forceUnderscore && $isPublic === false && $methodName{0} !== '_') {
            $error = 'Private method name "%s" must be prefixed with an underscore';
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $errorData);
            return;
        }

        // If it's not a private method, it must not have an underscore on the front.
        if ($this->forceUnderscore && $isPublic === true && $scopeSpecified === true && $methodName{0} === '_') {
            $error = '%s method name "%s" must not be prefixed with an underscore';
            $data  = array(
                      ucfirst($scope),
                      $errorData[0],
                     );
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        // If the scope was specified on the method, then the method must be
        // camel caps and an underscore should be checked for. If it wasn't
        // specified, treat it like a public method and remove the underscore
        // prefix if there is one because we cant determine if it is private or
        // public.
        $testMethodName = $methodName;
        if ($scopeSpecified === false && $methodName{0} === '_') {
            $testMethodName = substr($methodName, 1);
        }

        // Method name must be lowercase
        if (preg_match('|[A-Z]|', $testMethodName) !== 0) {
            $error = 'Method name "%s" must be lowercase';
            $phpcsFile->addError($error, $stackPtr, 'LowerCase', $errorData);
            return;
        }
        
        // Method name must be short
        if (strlen($testMethodName) > 30) {
            $error = 'Method name "%s" is too long';
            $phpcsFile->addWarning($error, $stackPtr, 'TooLong', $errorData);
            return;
        }

    }//end processTokenWithinScope()

    protected function processTokenOutsideScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === null) {
            // Ignore closures.
            return;
        }

        $errorData = array($functionName);

        // Is this a magic function. i.e., it is prefixed with "__".
        if (preg_match('|^__|', $functionName) !== 0) {
            $magicPart = strtolower(substr($functionName, 2));
            if (in_array($magicPart, $this->magicFunctions) === false) {
                $error = 'Function name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                $phpcsFile->addError($error, $stackPtr, 'FunctionDoubleUnderscore', $errorData);
            }

            return;
        }
    }//end processTokenOutsideScope()

}//end class