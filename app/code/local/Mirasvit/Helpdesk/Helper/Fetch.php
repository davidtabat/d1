<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Helper_Fetch extends Varien_Object
{
    /** @var  Mirasvit_Helpdesk_Model_Gateway */
    protected $gateway;

    /** @var  Mirasvit_Ddeboer_Imap_Connection */
    protected $connection;

    /** @var  Mirasvit_Ddeboer_Imap_Mailbox */
    protected $mailbox;

    public function isDev()
    {
        return Mage::getSingleton('helpdesk/config')->getDeveloperIsActive();
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Gateway $gateway
     *
     * @return bool
     */
    public function connect($gateway)
    {
        $this->gateway = $gateway;
        $flags = sprintf('/%s', $gateway->getProtocol());
        if ($gateway->getEncryption() == 'ssl') {
            $flags .= '/ssl';
        }
        $flags .= '/novalidate-cert';

        // echo $flags;die;
        $server = new Mirasvit_Ddeboer_Imap_Server($gateway->getHost(), $gateway->getPort(), $flags);
        if (function_exists('imap_timeout')) {
            imap_timeout(1, 20);
        }
        if (!$this->connection = $server->authenticate($gateway->getLogin(), $gateway->getPassword())) {
            return false;
        }

        $mailboxes = $this->connection->getMailboxNames();
        if (trim($gateway->getMailFolder()) != '' && in_array($gateway->getMailFolder(),  $mailboxes)) {
            $mailboxName = $gateway->getMailFolder();
        } else {
            if (in_array('INBOX',  $mailboxes)) {
                $mailboxName = 'INBOX';
            } elseif (in_array('Inbox',  $mailboxes)) {
                $mailboxName = 'Inbox';
            } else {
                $mailboxName = $mailboxes[0];
            }
        }

        $this->mailbox = $this->connection->getMailbox($mailboxName);

        return true;
    }

    public function close()
    {
        $this->connection->close();
    }

    /**
     * @param Mirasvit_Ddeboer_Imap_Message $message
     *
     * @return bool
     */
    public function getFromEmail($message)
    {
        // ???????? ???????? reply to, ???? ???? ?????? ?????????????????????????? ?????? from, ??.??. ???? ???????? ???? ?????????? ????????????????
        $fromEmail = false;
        if ($message->getReplyTo() && $message->getReplyTo()->getAddress()) {
            $fromEmail = $message->getReplyTo()->getAddress();
        } elseif ($message->getFrom()) {
            $fromEmail = $message->getFrom()->getAddress();
        }

        return $fromEmail;
    }

    /**
     * @param Mirasvit_Ddeboer_Imap_Message $message
     *
     * @return bool
     */
    public function getFromName($message)
    {
        // ???????? ???????? reply to, ???? ???? ?????? ?????????????????????????? ?????? from, ??.??. ???? ???????? ???? ?????????? ????????????????
        $fromName = 'unknown';
        if ($message->getReplyTo() && $message->getReplyTo()->getName()) {
            $fromName = $message->getReplyTo()->getName();
        } elseif ($message->getFrom()) {
            $fromName = $message->getFrom()->getName();
        }

        return $fromName;
    }

    /**
     * @param Mirasvit_Ddeboer_Imap_Message $message
     *
     * @return bool|Mirasvit_Helpdesk_Model_Email
     */
    public function createEmail($message)
    {
        try {
            $emails = Mage::getModel('helpdesk/email')->getCollection()
                ->addFieldToFilter('message_id', $message->getId())
                ->addFieldToFilter('from_email', $this->getFromEmail($message));

            if ($emails->count()) {
                return false;
            }

            $bodyHtml = $message->getBodyHtml();
            $bodyPlain = $message->getBodyText();
            if (!empty($bodyHtml)) {
                $format = Mirasvit_Helpdesk_Model_Config::FORMAT_HTML;
                $body = $bodyHtml;
            } else {
                $body = $bodyPlain;
                $format = Mirasvit_Helpdesk_Model_Config::FORMAT_PLAIN;
                $tags = array('<div', '<br', '<tr');
                foreach ($tags as $tag) {
                    if (stripos($body, $tag) !== false) {
                        $format = Mirasvit_Helpdesk_Model_Config::FORMAT_HTML;
                        break;
                    }
                }
            }

            $to = array();
            foreach ($message->getTo() as $email) {
                $to[] = $email->getAddress();
            }

            $cc = array();
            foreach ($message->getCc() as $copy) {
                $cc[] = $copy->mailbox.'@'.$copy->host;
            }

            $headers = $message->getHeaders()->toString();
            if (strlen($headers) > 10000) {
                $headers = substr($headers, 0, 10000); //headers includes attached files. they can have huge size.
            }
            $bodySizeLimit = ($format == Mirasvit_Helpdesk_Model_Config::FORMAT_HTML) ? 1000000 : 10000;

            if (strlen($body) > $bodySizeLimit) {
                $body = substr($body, 0, $bodySizeLimit);
            }

            $fromEmail = $this->getFromEmail($message);
            $senderName = $this->getFromName($message);

            $email = Mage::getModel('helpdesk/email')
                ->setMessageId($message->getId())
                ->setFromEmail($fromEmail)
                ->setSenderName($senderName)
                ->setToEmail(implode($to, ','))
                ->setCc(implode($cc, ', '))
                ->setSubject($message->getSubject())
                ->setBody($body)
                ->setFormat($format)
                ->setHeaders($headers)
                ->setIsProcessed(false);
            if ($this->gateway) { //may be null during tests
                    $email->setGatewayId($this->gateway->getId());
            }

            // All Auto-Submitted emails are marked as processed to prevent infinity cycles
            // http://www.iana.org/assignments/auto-submitted-keywords/auto-submitted-keywords.xhtml
            // https://tools.ietf.org/html/rfc3834
            // Automatic responses SHOULD NOT be issued in response to any
            // message which contains an Auto-Submitted header field (see below),
            // where that field has any value other than "no".
            //
            // https://tools.ietf.org/html/rfc3834#section-5
            // we accept auto-generated, because it maybe be an autogenerated notification about some event
            if (stripos($message->getHeaders()->toString(), 'Auto-Submitted: auto-replied') !== false
                || stripos($message->getHeaders()->toString(), 'Auto-Submitted: auto-notified') !== false
            ) {
                $email->setIsProcessed(true);

                return $email;
            }

            $email->save();
            $attachments = $message->getAttachments();

            if ($attachments) {
                foreach ($attachments as $a) {
                    $attachment = Mage::getModel('helpdesk/attachment');
                    $attachment->setName($a->getFilename())
                        ->setType($a->getType())
                        ->setSize($a->getSize())
                        ->setEmailId($email->getId())
                        ->save();
                    $attachment->setBody($a->getDecodedContent());
                }
            }

            return $email;
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
            Mage::log($e);

            return false;
        }
    }

    protected function fetchEmails()
    {
        $msgs = $errors = 0;
        $max = $this->gateway->getFetchMax();

        $messages = $this->mailbox->getMessages('UNSEEN');
        $emailsNumber = $this->mailbox->count();

        if ($limit = $this->gateway->getFetchLimit()) {
            $start = $emailsNumber - $limit + 1;
            if ($start < 1) {
                $start = 1;
            }
            for ($num = $start; $num <= $emailsNumber; $num++) {
                try { //we can have different errors during fetching of email. we don't want to stop fetching of all queue.
                    $message = $this->mailbox->getMessage($num);
                    if ($this->createEmail($message)) {
                        if ($this->gateway->getIsDeleteEmails()) {
                            $message->delete();
                            $this->mailbox->expunge();
                        }
                        $msgs++;
                    }
                    if ($max && $msgs >= $max) {
                        break;
                    }
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), 'helpdesk_error.log');
                }
            }
        } else {
            foreach ($messages as $message) {
                try { //we can have different errors during fetching of email. we don't want to stop fetching of all queue.
                    if ($this->createEmail($message)) {
                        if ($this->gateway->getIsDeleteEmails()) {
                            $message->delete();
                            $this->mailbox->expunge();
                        }
                        $msgs++;
                    }
                    if ($max && $msgs >= $max) {
                        break;
                    }
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), 'helpdesk_error.log');
                }
            }
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Gateway
     *
     * @throws Mage_Core_Exception
     *
     * @return bool
     */
    public function fetch($gateway)
    {
        if (!function_exists('imap_open')) {
            throw new Mage_Core_Exception("Can't fetch. Please, ask your hosting provider to enable IMAP extension in PHP configuration of your server.");
        }

        if (!$this->connect($gateway)) {
            return false;
        }
        $this->fetchEmails();
        $this->close();

        return true;
    }
}
