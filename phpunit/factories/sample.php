<?php
/**
 * @author OnTheGo Systems
 */
use League\FactoryMuffin\FactoryMuffin;

/** @var FactoryMuffin $fm */
$fm->define( 'Foo_Bar' )->setDefinitions( array(
	                                              'id'    => 'Message::makeSlug',
	                                              'group' => 'Message::makeSlug',
                                              ) );
