<?
CModule::AddAutoloadClasses(
    'oe.telegrambot',
    array(
        'OeTgBotEvents'       					=> 'lib/modulmanager/events.php',
        'OeTgBotOptions'       					=> 'lib/modulmanager/options.php',
        'Oe\Telegrambot\Options'       			=> 'lib/options.php',
        'Oe\Telegrambot\Entity\KeyboardTable'   => 'lib/entity/keyboard.php',
        'Oe\Telegrambot\Entity\WishlistTable'   => 'lib/entity/wishlist.php',
    	'Oe\Telegrambot\Entity\OrderTable'   	=> 'lib/entity/order.php',
    	'Oe\Telegrambot\Entity\PostingTable'   	=> 'lib/entity/posting.php',
        'Oe\Telegrambot\Entity\PostingLogTable' => 'lib/entity/posting_log.php',
    	'Oe\Telegrambot\Entity\PostingState'   	=> 'lib/entity/posting_state.php',

        'Oe\Telegrambot\Basket'        => 'lib/basket.php',
        'Oe\Telegrambot\Catalog'       => 'lib/catalog.php',
        'Oe\Telegrambot\CatalogUtils'  => 'lib/catalogutils.php',
        'Oe\Telegrambot\Msg'           => 'lib/msg.php',
        'Oe\Telegrambot\Order'         => 'lib/order.php',
        'Oe\Telegrambot\User'          => 'lib/user.php',
        'Oe\Telegrambot\Utils'         => 'lib/utils.php',
        'Oe\Telegrambot\Profile'       => 'lib/profile.php',
    	'Oe\Telegrambot\Search'        => 'lib/search.php',
        'Oe\Telegrambot\Main'          => 'lib/main.php',
    	'Oe\Telegrambot\Agent'         => 'lib/agent.php',
    	'Oe\Telegrambot\Feedback'      => 'lib/feedback.php',

        'Oe\Telegrambot\Statical\About'         => 'lib/statical/about.php',
        'Oe\Telegrambot\Statical\Help'          => 'lib/statical/help.php',
        'Oe\Telegrambot\Statical\Delivery'      => 'lib/statical/delivery.php',
        'Oe\Telegrambot\Statical\Garanty'       => 'lib/statical/garanty.php',
        'Oe\Telegrambot\Statical\News'          => 'lib/statical/news.php',
        'Oe\Telegrambot\Statical\Contacts'      => 'lib/statical/contacts.php'
    )
);
?>