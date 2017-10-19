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
            'mautic.infobip.sms.campaignbundle.subscriber' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.infobip.sms.model.sms',
                ],
            ],
            'mautic.infobip.sms.mauticinfobipsmsbundle.subscriber' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\EventListener\SmsSubscriber',
                'arguments' => [
                    'mautic.core.model.auditlog',
                    'mautic.page.model.trackable',
                    'mautic.page.helper.token',
                    'mautic.asset.helper.token',
                ],
            ],
            'mautic.infobip.sms.channel.subscriber' => [
                'class'     => \MauticPlugin\MauticInfoBipSmsBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
            'mautic.infobip.sms.message_queue.subscriber' => [
                'class'     => \MauticPlugin\MauticInfoBipSmsBundle\EventListener\MessageQueueSubscriber::class,
                'arguments' => [
                    'mautic.infobip.sms.model.sms',
                ],
            ],
            'mautic.infobip.sms.stats.subscriber' => [
                'class'     => \MauticPlugin\MauticInfoBipSmsBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        'forms' => [
            'mautic.infobip.form.type.sms' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\SmsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'infobipsms',
            ],
            'mautic.infobip.form.type.infobipsmsconfig' => [
                'class' => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\ConfigType',
                'alias' => 'infobipsmsconfig',
            ],
            'mautic.infobip.form.type.infobipsmssend_list' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\SmsSendType',
                'arguments' => 'router',
                'alias'     => 'infobipsmssend_list',
            ],
            'mautic.infobip.form.type.infobipsmssms_list' => [
                'class' => 'MauticPlugin\MauticInfoBipSmsBundle\Form\Type\SmsListType',
                'alias' => 'infobipsmssms_list',
            ],
        ],
        'helpers' => [
            'mautic.infobip.helper.sms' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Helper\SmsHelper',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'mautic.lead.model.lead',
                    'mautic.helper.phone_number',
                    'mautic.infobip.sms.model.sms',
                    'mautic.helper.integration',
                ],
                'alias' => 'infobipsms_helper',
            ],
        ],
        'other' => [
            'mautic.infobip.sms.api' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Api\InfoBipApi',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
                'alias' => 'infobipsms_api',
            ],
        ],
        'models' => [
            'mautic.infobip.sms.model.sms' => [
                'class'     => 'MauticPlugin\MauticInfoBipSmsBundle\Model\SmsModel',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.lead.model.lead',
                    'mautic.channel.model.queue',
                    'mautic.infobip.sms.api',
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
