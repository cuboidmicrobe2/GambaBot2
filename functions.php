<?php

declare(strict_types=1);

namespace GambaBot\Interaction {

    use Debug\CMD_FONT_COLOR;
    use Debug\CMDOutput;
    use Discord\Parts\Interactions\MessageComponent;
    use Discord\Parts\Interactions\ApplicationCommand;
    use Discord\Parts\User\User;
    use InvalidArgumentException;

    use stdClass;

    function getUserId(ApplicationCommand $interaction): string
    {
        return $interaction->member->user->id ?? $interaction->user->id;
    }

    function getUsername(ApplicationCommand|User $part): string
    {
        if ($part instanceof User) {
            return $part->global_name ?? $part->username;
        }

        if ($part->member !== null) {
            return $part->member->user->global_name ?? $part->member->user->username;
        }

        return $part->user->global_name ?? $part->user->username;
    }

    function getOptionValue(string $offset, ApplicationCommand $interaction): mixed
    {
        return $interaction->data->options->offsetExists($offset) ? $interaction->data->options->offsetGet($offset)->value : null;
    }

    function getCommandStrings(ApplicationCommand $interaction): ?stdClass
    {
        if (file_exists(__DIR__.'/Commands/content/strings.json')) {
            $strings = json_decode(file_get_contents(__DIR__.'/Commands/content/strings.json'));

            return $strings->{$interaction->data->name} ?? null;
        }
        echo CMDOutput::new()->add('strings.json is missing from '.__DIR__.'/content', CMD_FONT_COLOR::YELLOW), PHP_EOL;

        return null;
    }

    function insertStringValues(string $commandString, array $values): string
    {
        $patters = []; 
        foreach (array_keys($values) as $key) {
            $patters[] = '/\$('.$key.')/';
        }
        return preg_replace($patters, $values, $commandString);
    }    

    function buttonPresserId(MessageComponent $buttonInteraction): string
    {
        // return $buttonInteraction->message->interaction_metadata->user->id;
        return $buttonInteraction->member->user->id ?? $buttonInteraction->user->id;
    }

    function getButtonOwnerId(MessageComponent $buttonInteraction): string
    {
        return $buttonInteraction->message->interaction_metadata->user->id;
    }

    /**
     * @throws InvalidArgumentException if the Interaction does not belong to a valid component
     */
    function buttonPressedByOwner(MessageComponent $buttonInteraction): bool
    {
        // var_dump($buttonInteraction);
        $buttonPresserId = buttonPresserId($buttonInteraction);
        if ($buttonPresserId === null) {
            throw new InvalidArgumentException('This interaction does not contain $interaction-->message->interaction_metadata->user->id');
        }

        return getButtonOwnerId($buttonInteraction) === $buttonPresserId;
    }

    function buttonPressedByUser(string $uid, MessageComponent $buttonInteraction): bool
    {
        return buttonPresserId($buttonInteraction) === $uid;
    }
}

namespace GambaBot\Discord {

    /**
     * Create dicord @user from user id
     *
     * @deprecated use the abstract Format class
     */
    #[\Deprecated('use the abstract Format class')]
    function mention(string $uid): string
    {
        return '<@'.$uid.'>';
    }
}

namespace GambaBot\Discord\TextStyle {

    /**
     * @deprecated use the abstract Format class
     */
    #[\Deprecated('use the abstract Format class')]
    function code(string $text): string
    {
        return '`'.$text.'`';
    }

    /**
     * @deprecated use the abstract Format class
     */
    #[\Deprecated('use the abstract Format class')]
    function italic(string $text): string
    {
        return '*'.$text.'*';
    }

    /**
     * @deprecated use the abstract Format class
     */
    #[\Deprecated('use the abstract Format class')]
    function bold(string $text): string
    {
        return '**'.$text.'**';
    }

    /**
     * @deprecated use the abstract Format class
     */
    #[\Deprecated('use the abstract Format class')]
    function strikeThrough(string $text): string
    {
        return '~~'.$text.'~~';
    }
    
    /**
     * @deprecated use the abstract Format class
     */
    #[\Deprecated('use the abstract Format class')]
    function spoiler(string $text): string
    {
        return '||'.$text.'||';
    }
}

namespace GambaBot\Tools {
    function isImplementing(object $object, string $interface): bool
    {
        return in_array($interface, class_implements($object));
    }

    function isUsing(object $object, string $trait): bool
    {
        return in_array($trait, class_uses($object));
    }
}
