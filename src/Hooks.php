<?php

namespace MediaWiki\Extension\IndexNowNotifier;

use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\Hook\PageUndeleteCompleteHook;
use MediaWiki\Hook\PageMoveCompleteHook;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class Hooks implements
    PageSaveCompleteHook,
    PageDeleteCompleteHook,
    PageUndeleteCompleteHook,
    PageMoveCompleteHook
{
    public function onPageSaveComplete ( $wikiPage, $userIdentity, $summary, $flags, $revision, $editResult ): void {
        $hookName = 'onPageSaveComplete';
        $title = $wikiPage->getTitle();
        $namespace = $wikiPage->getNamespace();
        $user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity( $userIdentity );

        if ( $editResult->isNew() ) {
            Utils::handleCreate( $hookName, $title, $namespace, $user );
        }
        else {
            $isNull = $editResult->isNullEdit();
            $isMinor = $revision->isMinor();
            Utils::handleEdit( $hookName, $title, $namespace, $user, $isMinor, $isNull );
        }
    }

    public function onPageDeleteComplete ( $pageIdentity, $deleterAuthority, $reason, $pageID, $deletedRev, $logEntry, $archivedRevisionCount ): void {
        $hookName = 'onPageDeleteComplete';
        $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($pageIdentity);
        $title = $wikiPage->getTitle();
        $namespace = $wikiPage->getNamespace();
        $user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity($deleterAuthority->getUser());

        Utils::handleDelete( $hookName, $title, $namespace, $user );
    }

    public function onPageUndeleteComplete ( $pageIdentity, $restorerAuthority, $reason, $restoredRev, $logEntry, $restoredRevisionCount, $created, $restoredPageIds ): void {
        $hookName = 'onPageUndeleteComplete';
        $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($pageIdentity);
        $title = $wikiPage->getTitle();
        $namespace = $wikiPage->getNamespace();
        $user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity($restorerAuthority->getUser());

        Utils::handleCreate( $hookName, $title, $namespace, $user );
    }

    public function onPageMoveComplete ( $oldLink, $newLink, $userIdentity, $pageid, $redirid, $reason, $revision ): void {
        $hookName = 'onPageMoveComplete';
        $user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity( $userIdentity );
        $redirectCreated = $redirid !== 0;

        // if a redirect was created, onPageSaveComplete handles the update
        if (!$redirectCreated) {
            $oldTitle = Title::newFromLinkTarget( $oldLink );
            $oldNamespace = $oldLink->getNamespace();
            Utils::handleDelete( $hookName, $oldTitle, $oldNamespace, $user );
        }

        // onPageSaveComplete triggers for the new page, but appears as a null edit, so handle it here
        $newTitle = Title::newFromLinkTarget( $newLink );
        $newNamespace = $newLink->getNamespace();
        Utils::handleCreate( $hookName, $newTitle, $newNamespace, $user );
    }
}
