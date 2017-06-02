<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'services' => [
        'events' => [
            'mautic.sms.campaignbundle.subscriber' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.sms.model.sms',
                ],
            ],
            'mautic.sms.mauticinfobipsmsbundle.subscriber' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\EventListener\SmsSubscriber',
                'arguments' => [
                    'mautic.core.model.auditlog',
                    'mautic.page.model.trackable',
                    'mautic.page.helper.token',
                    'mautic.asset.helper.token',
                ],
            ],
            'mautic.sms.channel.subscriber' => [
                'class'     => \MauticPlugin\MauticInfoBipSmsBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
            'mautic.sms.message_queue.subscriber' => [
                'class'     => \MauticPlugin\MauticInfoBipSmsBundle\EventListener\MessageQueueSubscriber::class,
                'arguments' => [
                    'mautic.sms.model.sms',
                ],
            ],
            'mautic.sms.stats.subscriber' => [
                'class'     => \MauticPlugin\MauticInfoBipSmsBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.sms' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\SmsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'sms',
            ],
            'mautic.form.type.smsconfig' => [
                'class' => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\ConfigType',
                'alias' => 'smsconfig',
            ],
            'mautic.form.type.smssend_list' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\SmsSendType',
                'arguments' => 'router',
                'alias'     => 'smssend_list',
            ],
            'mautic.form.type.sms_list' => [
                'class' => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\SmsListType',
                'alias' => 'sms_list',
            ],
        ],
        'helpers' => [
            'mautic.helper.sms' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Helper\SmsHelper',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'mautic.lead.model.lead',
                    'mautic.helper.phone_number',
                    'mautic.sms.model.sms',
                    'mautic.helper.integration',
                ],
                'alias' => 'sms_helper',
            ],
        ],
        'other' => [
            'mautic.sms.api' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Api\InfoBipApi',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
                'alias' => 'sms_api',
            ],
        ],
        'models' => [
            'mautic.sms.model.sms' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Model\SmsModel',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.lead.model.lead',
                    'mautic.channel.model.queue',
                    'mautic.sms.api',
                ],
            ],
        ],
    ],
    'routes' => [
        'main' => [
            'mautic_sms_index' => [
                'path'       => '/infobipsms/{page}',
                'controller' => 'MauticInfoBipSmsBundle:Sms:index',
            ],
            'mautic_sms_action' => [
                'path'       => '/infobipsms/{objectAction}/{objectId}',
                'controller' => 'MauticInfoBipSmsBundle:Sms:execute',
            ],
            'mautic_sms_contacts' => [
                'path'       => '/infobipsms/view/{objectId}/contact/{page}',
                'controller' => 'MauticInfoBipSmsBundle:Sms:contacts',
            ],
        ],
        'public' => [
            'mautic_receive_sms' => [
                'path'       => '/infobipsms/receive',
                'controller' => 'MauticInfoBipSmsBundle:Api\SmsApi:receive',
            ],
        ],
        'api' => [
            'mautic_api_smsesstandard' => [
                'standard_entity' => true,
                'name'            => 'smses',
                'path'            => '/infobipsmses',
                'controller'      => 'MauticInfoBipSmsBundle:Api\SmsApi',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mautic.sms.smses' => [
                    'route'  => 'mautic_sms_index',
                    'access' => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent' => 'mautic.core.channels',
                    'checks' => [
                        'integration' => [
                            'InfoBip' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'priority' => 70,
                ],
            ],
        ],
    ],
    'parameters' => [
        'sms_enabled'              => false,
        'sms_username'             => null,
        'sms_password'             => null,
        'sms_sending_phone_number' => null,
        'sms_frequency_number'     => null,
        'sms_frequency_time'       => null,
    ],
];
