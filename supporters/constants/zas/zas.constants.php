<?php
namespace Zas;

/**
 * Contains all the constants for Zas helper
 */

class ZasConstant{
        /**
         * Errors
         */
        const ERR_CNE = "The specified class name contains errors or wasn't provided";

        /**
         * Commands constant
         */
        const   ZC_MAKE = "make",
                ZC_CLASS = "class",
                ZC_TRAIT = "trait",
                ZC_ABCLASS = "abstract",
                ZC_INFC = "interface",
                ZC_CONST = "constant",
                ZC_CONFIG = "config",
                ZC_UPD_ROOT = "update-root-path",
                ZC_ACTOR = "actor",
                ZC_SUPPORTER = "supporter",
                ZC_RUN = "run",
                ZC_HELP = "help";

        /**
         * Dash constants 
         */
        const   DASH_I = "-i",
                DASH_P = "-p",
                DASH_T = "-t",
                DASH_E = "-e",
                DASH_D = "-d",
                DASH_IN = "-in",
                DASH_DASH_F = "--f",
                DASH_DASH_ARG = "--arg";
        /** 
         * Word constants
         */
        const   WORD_FORE = "fore",
                WORD_BACK = "back";

        /**
         * Constants from the zas-config file
         */
        const   ZCFG_CLASS = "class",
                ZCFG_IFC   = "interface",
                ZCFG_ACLASS = "abstractClass",
                ZCFG_TRAIT = "trait",
                ZCFG_CONST = "constantsClass";

        /**
         * Constants for regex
         */
        const   R_START = 0,
                R_END = 1,
                R_ANYWHERE = 2;

        /**
         * Constants for formatter
         */
        const   SPACE_DEFAULT = 1,
                ENTER_DEFAULT = 1,
                TAB_DEFAULT = 0,
                INDENT_TAB = 1,
                FUNC_INDENT_TAB = 2;

        /**
         * Constants for text
         */
        const   TXT_PHP_INIT = "<?php\n\n// code...\n?>";

        private function __construct(){}
}
?>