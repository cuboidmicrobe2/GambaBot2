<?php

declare(strict_types = 1);

namespace GambaBot\Interaction {

    use Debug\CMD_FONT_COLOR;
    use Debug\CMDOutput;
    use Discord\Parts\Interactions\Interaction;

    function getUserId(Interaction $interaction) : string {
        return $interaction->member->user->id ?? $interaction->user->id;
    }

    function getUsername(Interaction $interaction) : string {
        if(isset($interaction->member)) return $interaction->member->user->global_name ?? $interaction->member->user->username;

        return $interaction->user->global_name ?? $interaction->user->username;
    }

    function getOptionValue(string $offset, Interaction $interaction) : mixed {
        return $interaction->data->options->offsetExists($offset) ? $interaction->data->options->offsetGet($offset)->value : null;
    }

    function getCommandStrings(Interaction $interaction) : ?array {
        if(file_exists(__DIR__.'/Commands/content/strings.json')) {
            $strings = json_decode(file_get_contents(__DIR__.'/Commands/content/strings.json'), true);
            
            return array_key_exists($interaction->data->name, $strings) ? $strings[$interaction->data->name] : null;
        }
        echo CMDOutput::new()->add('strings.json is missing from ' . __DIR__. '/content', CMD_FONT_COLOR::YELLOW), PHP_EOL;

        return null;
    }
}

namespace GambaBot\Discord {

    /**
     * Create dicord @user from user id
     */
    function mention(string $uid) : string {
        return '<@'.$uid.'>';
    }
}

namespace GambaBot\Discord\TextStyle {
    function code(string $text) : string { return '`'.$text.'`'; }
    function italic(string $text) : string { return '*'.$text.'*'; }
    function bold(string $text) : string { return '**'.$text.'**'; }
    function strikeThrough(string $text) : string { return '~~'.$text.'~~'; }
    function spoiler(string $text) : string { return '||'.$text.'||'; }
}



namespace GambaBot\Tools {
    function isImplementing(object $object, string $interface) : bool {
        return in_array($interface, class_implements($object));
    } 
}



