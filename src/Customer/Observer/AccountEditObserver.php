<?php

namespace Ls\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Ls\Omni\Helper\ContactHelper;

class AccountEditObserver implements ObserverInterface
{
    /** @var ContactHelper $contactHelper */
    private $contactHelper;

    /** @var \Magento\Framework\Message\ManagerInterface $messageManager */
    protected $messageManager;

    /** @var \Psr\Log\LoggerInterface $logger */
    protected $logger;

    /** @var \Magento\Customer\Model\Session $customerSession */
    protected $customerSession;

    /** @var \Magento\Framework\App\ActionFlag */
    protected $_actionFlag;

    /** @var \Magento\Framework\App\Response\RedirectInterface */
    protected $_redirectInterface;

    /**
     * AccountEditObserver constructor.
     * @param ContactHelper $contactHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirectInterface
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     */

    public function __construct(
        ContactHelper $contactHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Magento\Framework\App\ActionFlag $actionFlag
    )
    {
        $this->contactHelper = $contactHelper;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->_redirectInterface = $redirectInterface;
        $this->_actionFlag = $actionFlag;
    }

    /**
     * Customer Update Password through Omni End Point, currently we are only working on changing customer password and is not focusing on changing the customer account information.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Controller\Account\LoginPost\Interceptor $controller_action */
        $controller_action = $observer->getData('controller_action');
        $customer_edit_post = $controller_action->getRequest()->getParams();
        $customer = $this->customerSession->getCustomer();

        if (isset($customer_edit_post['change_password']) && $customer_edit_post['change_password']) {
            if ($customer_edit_post['password'] == $customer_edit_post['password_confirmation']) {
                $result = null;
                $result = $this->contactHelper->changePassword($customer, $customer_edit_post);
                if ($result) {
                    $this->messageManager->addSuccessMessage(
                        __('Your password has been updated.')
                    );
                } else {

                    $this->messageManager->addErrorMessage(
                        __('You have entered an invalid current password.')
                    );
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $observer->getControllerAction()->getResponse()->setRedirect($this->_redirectInterface->getRefererUrl());
                }

            } else {
                $this->messageManager->addErrorMessage(
                    __('Confirm password did not match.')
                );
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $observer->getControllerAction()->getResponse()->setRedirect($this->_redirectInterface->getRefererUrl());
            }
        }
        return $this;

    }
}
