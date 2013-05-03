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
 * @category  Debug
 * @author    Nisheeth Barthwal <nisheeth.barthwal@nbaztec.co.in>
 */
 class CodeIgniter_Sniffs_Debug_DebugCodeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('PHP');
    
    public $debugFunctions = array(
        'var_dump',
        'print_r',
    );

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(
            T_STRING,
            T_ECHO,
            T_PRINT,
            T_EXIT,
        );

    }//end register()

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     * @throws PHP_CodeSniffer_Exception If jslint.js could not be run
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (in_array($tokens[$stackPtr]['content'], $this->debugFunctions) === TRUE) {
            $error = 'No debug code is allowed : "%s"';
            $data  = array($tokens[$stackPtr]['content']);
            $phpcsFile->addWarning($error, $stackPtr, 'DebugCode', $data);
        }

    }//end process()

}//end class