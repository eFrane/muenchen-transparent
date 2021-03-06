<?php

class Reindex_StadtraetInnenCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (isset($args[0]) && $args[0] > 0) {
            $parser = new StadtraetInnenParser();
            $parser->setParseAlleAntraege(true);
            $parser->parse($args[0]);
        } else {
            $parser = new StadtraetInnenParser();
            $parser->parseUpdate();
        }
    }
}
