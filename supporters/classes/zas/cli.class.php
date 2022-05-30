<?php
    // Comments with #.# are required by `zas` for code insertion.

    namespace Zas;

    #uns#


    class Cli extends ZasHelper  {

        # use traits
        #ut#

        /**
         * Handles the commands
         * Returns true if the command was found or false otherwise
         * @param int $argc
         * @param array $argv
         * 
         * @return void
         */
        public function process(int &$argc, array &$argv){
            
            if($argc < 2){
                $this->printHelp();
                return true;
            }

            # show list
            $mainCommand = strtolower($argv[1]);

            switch($mainCommand){
                case ZasConstants::ZC_MAKE:
                    {
                        $this->execMake($argc, $argv);
                        return true;
                    }
                case ZasConstants::ZC_UPD_ROOT:
                    {
                        $this->updateRootPath();
                        return true;
                    }
                case ZasConstants::ZC_RUN:
                    {
                        $this->run($argc, $argv);
                        return true;
                    }
                case ZasConstants::ZC_HELP:
                    {
                        $this->printHelp();
                        return true;
                    }
            }
            return false;
        }
    }

?>