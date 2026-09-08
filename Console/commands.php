<?php

declare(strict_types=1);

namespace core
{

    use Cli\Attribute\Description;
    use Debug\Console\CMDOutput;
    use Debug\Console\FontColor;
    use ReflectionException;
    use ReflectionFunction;

    #[Description(
        description: 'Displays this message',
        usage: \COMMAND.' help -h [COMMAND NAME]',
        parameters: ['h' => 'command name']
    )]
    function help($h = null): void
    {
        if ($h === null) {
            new Description(
                description: 'Use the help command do display information about commands'
                    .PHP_EOL
                    .'  Check '.__FILE__.' for all commands',
                usage: \COMMAND.' help -h [COMMAND NAME] (try: php gacha help -h core:help)',
                parameters: ['h' => 'command name']
            )->printFullDescription();
            var_dump(get_defined_functions());
            return;
        }

        $functionName = str_replace(':', '\\', $h);

        try {
            $refl = new ReflectionFunction($functionName);
            $description = array_first($refl->getAttributes(Description::class));
            /** @var \ReflectionAttribute<Description> */
            $description?->newInstance()->printFullDescription();
        } catch (ReflectionException $ex) {
            echo CMDOutput::create(FontColor::BRIGHT_RED, $ex->getMessage()), PHP_EOL;
        }
    }

    #[Description(
        description: 'This command is used for testing',
        usage: \COMMAND.' test',
    )]
    function test() {
        echo 'testing testing testing',PHP_EOL;
    }
}

namespace bot
{
    function say($m): void
    {
        echo 'The bot says: ', $m, PHP_EOL;
    }
}

namespace coins
{

    use Cli\Attribute\Description;
    use Gamba\Loot\Item\Inventory;
    use PDO;

    $inventory = fn (string $uid): Inventory => new Inventory(
        owner: $uid,
        database: PDO::connect(
            dsn: 'mysql:host='.$_ENV['DB_HOSTNAME'].';port=3306;dbname=gamba_inventories',
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD']
        )
    );

    #[Description(
        description: 'Sets a users coins',
        usage: \COMMAND.' coins:set -u [USER ID] -c [COIN AMOUNT]',
        parameters: ['u' => 'The users discord ID', 'c' => 'Amount of coins']
    )]
    function set($u, $c): void
    {   
        global $inventory;
        /** @var \Closure(string $uid): Inventory $inventory */
        $inventory((string) $u)->setCoins((int) $c);
    }

    #[Description(
        description: 'Adds coins to a user',
        usage: \COMMAND.' coins:add -u [USER ID] -c [COIN AMOUNT]',
        parameters: ['u' => 'The users discord ID', 'c' => 'Amount of coins']
    )]
    function add($u, $c): void
    {
        global $inventory;
        /** @var \Closure(string $uid): Inventory $inventory */
        $inventory((string) $u)->addCoins((int) $c);
    }
}

namespace commands
{
    use Debug\Console\CMDOutput;
    use Debug\Console\FontColor;
    use Cli\Attribute\Description;

    #[Description(
        description: 'Disables bot commands that use inventories',
        usage: \COMMAND.' commands:disable',
    )]
    function disable() {
        echo CMDOutput::create(FontColor::BRIGHT_YELLOW, 'Commands disabled (wip)'), PHP_EOL;
    }

    #[Description(
        description: 'Reenables bot commands that use inventories',
        usage: \COMMAND.' commands:enable',
    )]
    function enable() {
        echo CMDOutput::create(FontColor::BRIGHT_GREEN, 'Commands enabled (wip)'), PHP_EOL;
    }
}

namespace docker
{
    function build(): void
    {
        shell_exec('docker compose up --build -d');
    }

    function up(): void
    {
        shell_exec('docker compose up');
    }

    function down(): void
    {
        shell_exec('docker compose down');
    }
}
