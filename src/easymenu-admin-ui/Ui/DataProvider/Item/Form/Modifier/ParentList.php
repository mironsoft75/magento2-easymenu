<?php

declare(strict_types=1);

namespace AMF\EasyMenuAdminUi\Ui\DataProvider\Item\Form\Modifier;

use AMF\EasyMenuAdminUi\Model\Locator\LocatorInterface;
use AMF\EasyMenuAdminUi\Ui\Component\Item\Form\ParentList as ParentOptions;
use AMF\EasyMenuApi\Api\Data\ItemInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * ParentList Modifier
 */
class ParentList implements ModifierInterface
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ParentOptions
     */
    private $parentOptions;

    /**
     * ParentList constructor.
     *
     * @param LocatorInterface $locator
     * @param ParentOptions $parentList
     */
    public function __construct(
        LocatorInterface $locator,
        ParentOptions $parentList
    ) {
        $this->locator = $locator;
        $this->parentOptions = $parentList;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->addParentField($meta);
    }

    /**
     * Add Parent Field metadata
     *
     * @param array $meta
     *
     * @return array
     */
    private function addParentField(array $meta): array
    {
        $menuItem = $this->locator->getMenuItem();
        $parentField = ItemInterface::PARENT_ID;
        $options = $this->parentOptions->toOptionArray();

        $isParentFieldVisible = $this->isParentFieldVisible($menuItem, $options);

        $meta['general']['children'][$parentField]['arguments']['data']['options'] = $options;
        $meta['general']['children'][$parentField]['arguments']['data'] = [
            'options' => $options,
            'config' => $this->getParentListConfiguration($isParentFieldVisible),
        ];

        if (! $menuItem->getId()) {
            $meta = $this->addParentDefaultValue($meta);
        }

        return $meta;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    private function addParentDefaultValue(array $meta)
    {
        $parentField = ItemInterface::PARENT_ID;
        $meta['general']['children'][$parentField]['arguments']['data']['config']['value'] = $this->getParentId();

        return $meta;
    }

    /**
     * Check if we should show parent field
     *
     * @param ItemInterface $menuItem
     * @param array $options
     *
     * @return bool
     */
    private function isParentFieldVisible(ItemInterface $menuItem, array $options): bool
    {
        return $menuItem->getId() || count($options) ? true : false;
    }

    /**
     * Retrieve Parent List config options
     *
     * @param bool $isParentFieldVisible
     *
     * @return array
     */
    private function getParentListConfiguration(bool $isParentFieldVisible): array
    {
        return [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Select::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => __('Parent Item'),
            'component' => 'AMF_EasyMenuAdminUi/js/components/parent-list',
            'elementTmpl' => 'ui/grid/filters/elements/ui-select',
            'filterOptions' => true,
            'showCheckbox' => false,
            'disableLabel' => true,
            'multiple' => false,
            'levelsVisibility' => 1,
            'sortOrder' => 30,
            'visible' => $isParentFieldVisible,
            'validation' => ['required-entry' => true],
        ];
    }

    /**
     * Return Parent id from request
     *
     * @return int
     */
    private function getParentId(): int
    {
        return (int) $this->locator->getMenuItem()->getParentId();
    }
}
