<?php


/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticInfoBipSmsBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class InfoBipIntegration.
 */
class InfoBipIntegration extends AbstractIntegration
{
    /**
     * @var bool
     */
    protected $coreIntegration = true;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'InfoBip';
    }

    public function getIcon()
    {
        return 'plugins/MauticInfoBipSmsBundle/Assets/img/InfoBip.png';
    }

    public function getSecretKeys()
    {
        return ['password'];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'username' => 'mautic.sms.config.form.sms.username',
            'password' => 'mautic.sms.config.form.sms.password',
        ];
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'features') {
            $builder->add(
                'sending_phone_number',
                'text',
                [
                    'label'      => 'mautic.sms.config.form.sms.sending_phone_number',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                ]
            );
            $builder->add('frequency_number', 'number',
                [
                    'precision'  => 0,
                    'label'      => 'mautic.sms.list.frequency.number',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class' => 'form-control frequency',
                    ],
                ]);
            $builder->add('frequency_time', 'choice',
                [
                    'choices' => [
                        'DAY'   => 'day',
                        'WEEK'  => 'week',
                        'MONTH' => 'month',
                    ],
                    'label'      => 'mautic.lead.list.frequency.times',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'multiple'   => false,
                    'attr'       => [
                        'class' => 'form-control frequency',
                    ],
                ]);
        }
    }
}
