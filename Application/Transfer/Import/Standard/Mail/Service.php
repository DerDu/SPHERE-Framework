<?php

namespace SPHERE\Application\Transfer\Import\Standard\Mail;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Standard\Mail
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     * @param null $Data
     *
     * @return IFormInterface|Danger|string
     */
    public function createMailsFromFile(IFormInterface $Form = null, UploadedFile $File = null, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (!($tblType = \SPHERE\Application\Contact\Mail\Mail::useService()->getTypeById($Data['Type']))) {
            $Form->setError('Data[Type]', 'Bitte geben Sie einen Typ an');
            return $Form;
        }
        $isAccountAlias = isset($Data['IsAccountAlias']);
        $isTest = isset($Data['IsTest']);

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Benutzername' => null,
                    'Vorname' => null,
                    'Nachname' => null
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $countPersons = 0;
                $countMissingPersons = 0;
                $countDuplicatePersons = 0;
                $countAccounts = 0;
                $countMissingAccounts = 0;
                $countAddMail = 0;

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        $mail = trim($Document->getValue($Document->getCell($Location['Benutzername'], $RunY)));
                        $mail = str_replace(' ', '', $mail);
                        $addMail = false;
                        $tblPerson = false;
                        if ($firstName !== '' && $lastName !== '' && $mail != '') {
                            if (($tblPersonList = Person::useService()->getPersonAllByFirstNameAndLastName($firstName, $lastName))) {
                                if (count($tblPersonList) == 1) {
                                    $countPersons++;
                                    $addMail = true;
                                } else {
                                    $countDuplicatePersons++;
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde mehrmals gefunden';
                                }
                            } elseif (($tblPersonList = Person::useService()->getPersonAllByFirstNameAndLastName($this->refactorName($firstName), $this->refactorName($lastName)))) {
                                if (count($tblPersonList) == 1) {
                                    $countPersons++;
                                    $addMail = true;
                                } else {
                                    $countDuplicatePersons++;
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde mehrmals gefunden';
                                }
                            } elseif (($tblPersonList = Person::useService()->getPersonListLikeFirstNameAndLastName($this->refactorName($firstName), $this->refactorName($lastName)))) {
                                if (count($tblPersonList) == 1) {
                                    $countPersons++;
                                    $addMail = true;
                                } else {
                                    $countDuplicatePersons++;
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde mehrmals gefunden';
                                }
                            } else {
                                $countMissingPersons++;
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde nicht gefunden';
                            }

                            if ($addMail) {
                                if ($isAccountAlias) {
                                    $addMail = false;
                                    // findAccounts
                                    if (($tblPerson = current($tblPersonList))
                                        && ($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))
                                        && count($tblAccountList) == 1
                                    ) {
                                        $countAccounts++;
                                        if (!$isTest) {
                                            if (($tblAccount = current($tblAccountList))
                                                && Account::useService()->changeUserAlias($tblAccount, $mail)
                                            ) {
                                                $addMail = true;
                                            } else {
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                                    . ' Alias konnte nicht am Benutzerkonto gespeichert werden.';
                                            }
                                        }
                                    } else {
                                        $countMissingAccounts++;
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                            . ' besitzt kein Benutzerkonto';
                                    }
                                }

                                if ($addMail && $tblPerson && !$isTest) {
                                    // alle Emailadressen der Person mit isAccountUserAlias zurücksetzen
                                    if ($isAccountAlias
                                        && (($tblMailToPersonList = \SPHERE\Application\Contact\Mail\Mail::useService()->getMailAllByPerson($tblPerson)))
                                    ) {
                                        foreach ($tblMailToPersonList as $tblToPerson) {
                                            if ($tblToPerson->isAccountUserAlias()) {
                                                \SPHERE\Application\Contact\Mail\Mail::useService()->updateMailToPersonService(
                                                    $tblToPerson, $tblToPerson->getTblMail()->getAddress(), $tblToPerson->getTblType(), $tblToPerson->getRemark(), false
                                                );
                                            }
                                        }
                                    }

                                    if (\SPHERE\Application\Contact\Mail\Mail::useService()->insertMailToPerson($tblPerson, $mail, $tblType, '', $isAccountAlias)) {
                                        $countAddMail++;
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Emailadresse konnte nicht angelegt werden.';
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Emailadresse wurde nicht angelegt, da sie nicht vollständig ist.';
                        }
                    }

                    return
                        new Success('Es wurden ' . $countPersons . ' Personen erfolgreich gefunden.') .
                            ($countAccounts > 0 ? new Success('Es wurden ' . $countAccounts . ' Benutzerkonten gefunden') : '') .
                            ($countAddMail > 0 ? new Success('Es wurden ' . $countAddMail . ' Emailadressen erfolgreich angelegt') : '') .
                            ($countDuplicatePersons > 0 ? new Warning($countDuplicatePersons . ' Doppelte Personen gefunden') : '') .
                            ($countMissingPersons > 0 ? new Warning($countMissingPersons . ' Personen nicht gefunden') : '') .
                            ($countMissingAccounts > 0 ? new Warning($countMissingAccounts . ' Benutzerkonten nicht gefunden') : '') .
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                )
                            ))));
                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function refactorName($name)
    {
        $name = str_replace('ae', 'ä', $name);
        $name = str_replace('ue', 'ü', $name);
        $name = str_replace('oe', 'ö', $name);
        $name = str_replace('ss', 'ß', $name);

        $name = str_replace('Ae', 'Ä', $name);
        $name = str_replace('Ue', 'Ü', $name);
        $name = str_replace('Oe', 'Ö', $name);

        return $name;
    }
}