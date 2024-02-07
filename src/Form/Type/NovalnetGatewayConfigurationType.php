<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Form\Type;

use Novalnet\SyliusNovalnetPaymentPlugin\Client\NovalnetApiClient;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCode;
use Novalnet\SyliusNovalnetPaymentPlugin\Validator\Constraint\NovalnetCredentials;
use Payum\Core\Bridge\Spl\ArrayObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class NovalnetGatewayConfigurationType extends AbstractType
{
    /** @var array */
    private $tariff;

    public function __construct(
        private CustomLangCode $customLangCode,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'api_signature',
                TextType::class,
                [
                    'label' => 'novalnet_sylius_novalnet_payment_plugin.ui.api_signature',
                    'attr' => [
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'novalnet_sylius_novalnet_payment_plugin.ui.api_signature_blank',
                            'groups' => 'sylius',
                        ]),
                    ],
                ],
            )
            ->add(
                'api_access_key',
                TextType::class,
                [
                    'label' => 'novalnet_sylius_novalnet_payment_plugin.ui.api_access_key',
                    'attr' => [
                        'autocomplete' => 'off',
                    ],
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'novalnet_sylius_novalnet_payment_plugin.ui.api_access_key_blank',
                            'groups' => 'sylius',
                        ]),
                    ],
                ],
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $config = ArrayObject::ensureArrayObject($data);
            if (!$config->validateNotEmpty(['api_access_key', 'api_signature'], false)) {
                return;
            }

            $novalnetApiClient = new NovalnetApiClient($config);
            $configResponse = $novalnetApiClient->getMerchantDetails([
                'merchant' => [
                    'signature' => $config->offsetGet('api_signature'),
                ],
                'custom' => [
                    'lang' => $this->customLangCode->getLangCode(),
                ],
            ]);

            if ($novalnetApiClient->isSuccessApi($configResponse)) {
                $form = $event->getForm();
                foreach ($configResponse['merchant']['tariff'] as $tariffID => $tariffData) {
                    $this->tariff[$tariffData['name']] = $tariffID;
                }
                $form
                    ->add(
                        'payment_tariff',
                        ChoiceType::class,
                        [
                            'choices' => $this->tariff,
                            'label' => 'novalnet_sylius_novalnet_payment_plugin.ui.payment_tariff',
                            'data' => $config->offsetGet('payment_tariff') ?? '',
                        ],
                    )
                    ->add(
                        'use_authorize',
                        ChoiceType::class,
                        [
                            'choices' => [
                                'Capture' => false,
                                'Authorize' => true,
                            ],
                            'required' => true,
                            'label' => 'novalnet_sylius_novalnet_payment_plugin.ui.payment_action',
                            'data' => $config->offsetGet('use_authorize') ?? false,
                        ],
                    )
                    ->add(
                        'test_mode',
                        CheckboxType::class,
                        [
                            'label' => 'novalnet_sylius_novalnet_payment_plugin.ui.test_mode',
                            'required' => false,
                        ],
                    )
                    ->add(
                        'allow_test_notification',
                        CheckboxType::class,
                        [
                            'label' => 'novalnet_sylius_novalnet_payment_plugin.ui.test_webhook',
                            'required' => false,
                        ],
                    )
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('constraints', [
            new NovalnetCredentials([
                'groups' => ['sylius'],
            ]),
        ]);
    }
}
