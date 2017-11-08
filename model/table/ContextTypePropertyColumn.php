<?php

namespace oat\taoOutcomeUi\model\table;


class ContextTypePropertyColumn extends \tao_models_classes_table_PropertyColumn
{
    const CONTEXT_TYPE_TEST_TAKER = 'test_taker';
    const CONTEXT_TYPE_DELIVERY = 'delivery';

    public $contextType;

    /**
     * ContextAwarePropertyColumn constructor.
     *
     * @param string                        $contextType
     * @param \core_kernel_classes_Property $property
     */
    public function __construct($contextType, \core_kernel_classes_Property $property)
    {
        parent::__construct($property);

        $this->setContextType($contextType);
    }

    /**
     * @param array $data
     * @return ContextTypePropertyColumn
     */
    protected static function fromArray($data)
    {
        return new self($data['contextType'], new \core_kernel_classes_Property($data['prop']));
    }

    /**
     * @param string $contextType
     * @throws \common_exception_InvalidArgumentType
     */
    public function setContextType($contextType)
    {
        if (!in_array($contextType, [self::CONTEXT_TYPE_TEST_TAKER, self::CONTEXT_TYPE_DELIVERY])) {
            throw new \common_exception_InvalidArgumentType('Not valid context type "'. $contextType .'"');
        }

        $this->contextType = $contextType;
    }

    /**
     * @return string
     */
    public function getContextType()
    {
        return $this->contextType;
    }

    /**
     * @return bool
     */
    public function isTestTakerType()
    {
        return $this->getContextType() == self::CONTEXT_TYPE_TEST_TAKER;
    }

    /**
     * @return bool
     */
    public function isDeliveryType()
    {
        return $this->getContextType() == self::CONTEXT_TYPE_DELIVERY;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        $result['contextType'] = $this->getContextType();

        return $result;
    }
}