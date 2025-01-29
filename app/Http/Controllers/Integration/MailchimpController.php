<?php

namespace App\Http\Controllers\Integration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Classes\Mailchimp\MailchimpTokenAuthentication;
use App\Models\Integration;
use App\Models\Contact;
use Illuminate\Support\Facades\Crypt;
use App\Traits\TagsTrait;
use App\Models\TagFolder;
use App\Constants;
use App\Models\Tag;

class MailchimpController extends Controller {

    use TagsTrait;

    const DEFAULT_OFFSET = 10;
    private $mailchimp;

    public function __construct(MailchimpTokenAuthentication $mailchimp) {
        $this->mailchimp = $mailchimp;
    }

    /**
     * Retrieves token from database and setup to mailchimp class
     * @param Integer $id
     */
    private function setToken($id) {
        $integration = Integration::findOrFail($id);
        $token = $integration->values->where('key', 'API_KEY')->first()->value;
        $this->mailchimp->setToken($token);
    }

    /**
     * Shows All mailchimp lists availables
     * @param Integer $id
     * @return View
     */
    public function index($id) {
        $this->setToken($id);
        $lists = $this->mailchimp->getLists()->lists;
        //dd($lists);
        $data = ['lists' => $lists, 'id' => $id];
        return view('integration.apps.mailchimp.index')->with($data);
    }

    /**
     * Shows all members in selected list
     * @param Integer $id
     * @param String $listId
     * @param Request $request
     * @return View
     */
    public function members($id, $listId, Request $request) {
        $offset = array_get($request, 'offset', 0);
        $this->setToken($id);
        $list = $this->mailchimp->getMembers($listId, $offset);
        
        $totalOfPages = ceil($list->total_items / self::DEFAULT_OFFSET);
        $pagination = [];
        
        for($i = 0; $i < $totalOfPages; $i++){
            $page = new \stdClass();
            $page->number = $i+1;
            $page->offset = $i * self::DEFAULT_OFFSET;
            array_push($pagination, $page);
        }
        
        $data = [
            'list' => $list,
            'id' => $id,
            'listName' => array_get($request, 'listname'),
            'pagination' => $pagination
        ];
        return view('integration.apps.mailchimp.members')->with($data);
    }
    
    /**
     * Route: mailchimp.addmembers
     * @param Integer $id
     * @param String $listId
     * @param Request $request
     * @return View
     */
    public function addMembers($id, $listId, Request $request) {
        $this->setToken($id);
        $contacts = Contact::select()->paginate();
        $data = [
            'contacts' => $contacts,
            'id' => $id,
            'listId' => $listId,
            'listName' => array_get($request, 'listname'),
        ];

        return view('integration.apps.mailchimp.addmembers')->with($data);
    }

    /**
     * Route: mailchimp.addtags
     * @param Integer $id
     * @param String $listId
     * @param Request $request
     * @return View
     */
    public function addTags($id, $listId, Request $request) {
        $this->setToken($id);
        $root = TagFolder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $folder = array_get($root, 'id');
        if ($request->has('folder')) {
            $folder = array_get($request, 'folder');
        }

        $data = $this->getDataTree($root, $folder);
        array_set($data, 'id', $id);
        array_set($data, 'list', $listId);
        array_set($data, 'listName', array_get($request, 'listname'));

        if (is_null($data['root'])) {
            abort(404);
        }
        return view('integration.apps.mailchimp.addtags')->with($data);
    }
    
    /**
     * Route: mailchimp.deletetags
     * @param Integer $id
     * @param String $listId
     * @param Request $request
     * @return type
     */
    public function deleteTags($id, $listId, Request $request) {
        $root = TagFolder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $folder = array_get($root, 'id');
        if ($request->has('folder')) {
            $folder = array_get($request, 'folder');
        }

        $data = $this->getDataTree($root, $folder);
        array_set($data, 'id', $id);
        array_set($data, 'list', $listId);
        array_set($data, 'listName', array_get($request, 'listname'));

        if (is_null($data['root'])) {
            abort(404);
        }
        return view('integration.apps.mailchimp.deletetags')->with($data);
    }

    /**
     * Route: mailchimp.store
     * @param Integer $id
     * @param Integer $listId
     * @param Request $request
     * @return Redirect
     */
    public function store($id, $listId, Request $request) {
        $this->setToken($id);
        try {
            if ($request->has('cid')) {
                $response = $this->subscribeContact(array_get($request, 'cid'), $listId);
            }

            if ($request->has('tid')) {
                $response = $this->subscribeTag(array_get($request, 'tid'), $listId);
            }

            return redirect()->route('mailchimp.members', ['id' => $id, 'list' => $listId, 'listname' => array_get($request, 'listname')])
                            ->with('response', $response);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect('cheating');
        }
        abort(500);
    }
    
    /**
     * Route: mailchimp.unsubscribecontact
     * @param Integer $id
     * @param Integer $listId
     * @param String $member
     * @param Request $request
     * @return Redirect
     */
    public function unsubscribeContact($id, $listId, $member, Request $request) {
        $this->setToken($id);
        $response = $this->mailchimp->unsubscribeMember($listId, $member);
        return redirect()->route('mailchimp.members', ['id' => $id, 'list' => $listId, 'listname' => array_get($request, 'listname')])
                            ->with('response', $response);
    }
    
    /**
     * 
     * @param type $id
     * @param Integer $listId
     * @param Encrypted String $tagId
     * @param Request $request
     * @return Redirect
     */
    public function unsubscribeTag($id, $listId, $tagId, Request $request) {
        try {
            $tid = Crypt::decrypt($tagId);
        } catch (Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect()->route('cheating');
        }
        
        $this->setToken($id);
        $tag = Tag::findOrFail($tid);
        $batch = [];
        foreach ($tag->contacts as $contact) {
            $data = $this->prepareMemberData($contact);
            array_push($batch, $this->prepareBatchData($contact, $listId, $data, 'DELETE'));
        }

        $operations = [];
        array_set($operations, 'operations', $batch);
        $response = $this->mailchimp->subscribeMembers($operations);
        
        return redirect()->route('mailchimp.members', ['id' => $id, 'list' => $listId, 'listname' => array_get($request, 'listname')])
                            ->with('response', $response);
    }
    
    public function addList($id) {
        $data = ['id' => $id];
        return view('integration.apps.mailchimp.addlist')->with($data);
    }
    
    public function storeList($id, Request $request) {
        $this->setToken($id);
        $contact = $request->only(['company', 'address1', 'city', 'state', 'zip', 'country']);
        $campaign = $request->only(['from_name', 'from_email', 'subject', 'language']);
        
        $list = [];
        array_set($list, 'name', array_get($request, 'name'));
        array_set($list, 'contact', $contact);
        array_set($list, 'permission_reminder', array_get($request, 'permission_reminder'));
        array_set($list, 'campaign_defaults', $campaign);
        array_set($list, 'email_type_option', false);
        
        $response = $this->mailchimp->setList($list);
        return redirect()->route('mailchimp.index', ['id' => $id])->with(['response' => $response]);
    }
    
    private function subscribeTag($tid, $listId) {
        $tagId = Crypt::decrypt($tid);
        $tag = Tag::findOrFail($tagId);

        $batch = [];
        foreach ($tag->contacts as $contact) {
            $data = $this->prepareMemberData($contact);
            array_push($batch, $this->prepareBatchData($contact, $listId, $data, 'PUT'));
        }

        $operations = [];
        array_set($operations, 'operations', $batch);
        $response = $this->mailchimp->subscribeMembers($operations);
        return $response;
    }

    private function subscribeContact($cid, $listId) {
        try {
            $contactId = Crypt::decrypt($cid);
        } catch (Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect()->route('cheating');
        }
        
        $contact = Contact::findOrFail($contactId);
        $data = $this->prepareMemberData($contact);
        $response = $this->mailchimp->subscribeMember($listId, $data);
        return $response;
    }

    private function prepareMemberData($contact) {
        $data = [];
        if (is_null(array_get($contact, 'last_name'))) {
            array_set($contact, 'last_name', "");
        }
        array_set($data, 'email_address', array_get($contact, 'email_1'));
        array_set($data, 'merge_fields.FNAME', array_get($contact, 'first_name'));
        array_set($data, 'merge_fields.LNAME', array_get($contact, 'last_name'));
        array_set($data, 'status', 'subscribed');
        return $data;
    }

    private function prepareBatchData($contact, $list, $data, $method = 'PUT') {
        $operations = [];
        $path = "lists/$list/members/" . md5( strtolower(array_get($contact, 'email_1')) );
        array_set($operations, 'method', $method);
        array_set($operations, 'path', $path);
        array_set($operations, 'body', json_encode($data));

        return $operations;
    }

}
