<?php
namespace App\Http\Controllers;

use Assets;
use Illuminate\Support\Facades\Session;
use Input;
use Lang;
use App\Models\License;
use App\Models\Asset;
use App\Models\User;
use App\Models\Actionlog;
use DB;
use App\Models\LicenseSeat;
use App\Models\Company;
use Validator;
use View;
use Response;
use Slack;
use Config;
use App\Helpers\Helper;
use Auth;
use Gate;
use Illuminate\Http\Request;

/**
 * This controller handles all actions related to Licenses for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class LicensesController extends Controller
{

    /**
    * Returns a view that invokes the ajax tables which actually contains
    * the content for the licenses listing, which is generated in getDatatable.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see LicensesController::getDatatable() method that generates the JSON response
    * @since [v1.0]
    * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->authorize('view', License::class);
        return view('licenses/index');
    }


    /**
    * Returns a form view that allows an admin to create a new licence.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see AccessoriesController::getDatatable() method that generates the JSON response
    * @since [v1.0]
    * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorize('create', License::class);
        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No'
        ];

        return view('licenses/edit')
            //->with('license_options',$license_options)
            ->with('depreciation_list', Helper::depreciationList())
            ->with('supplier_list', Helper::suppliersList())
            ->with('maintained_list', $maintained_list)
            ->with('company_list', Helper::companyList())
            ->with('manufacturer_list', Helper::manufacturerList())
            ->with('item', new License);

    }


    /**
     * Validates and stores the license form data submitted from the new
     * license form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getCreate() method that provides the form view
     * @since [v1.0]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', License::class);
        // create a new model instance
        $license = new License();
        // Save the license data
        $license->company_id        = Company::getIdForCurrentUser($request->input('company_id'));
        $license->depreciation_id   = $request->input('depreciation_id');
        $license->expiration_date   = $request->input('expiration_date');
        $license->license_email     = $request->input('license_email');
        $license->license_name      = $request->input('license_name');
        $license->maintained        = $request->input('maintained', 0);
        $license->manufacturer_id   = $request->input('manufacturer_id');
        $license->name              = $request->input('name');
        $license->notes             = $request->input('notes');
        $license->order_number      = $request->input('order_number');
        $license->purchase_cost     = $request->input('purchase_cost');
        $license->purchase_date     = $request->input('purchase_date');
        $license->purchase_order    = $request->input('purchase_order');
        $license->purchase_order    = $request->input('purchase_order');
        $license->reassignable      = $request->input('reassignable', 0);
        $license->seats             = $request->input('seats');
        $license->serial            = $request->input('serial');
        $license->supplier_id       = $request->input('supplier_id');
        $license->termination_date  = $request->input('termination_date');
        $license->user_id           = Auth::id();

        if ($license->save()) {
            return redirect()->route("licenses.index")->with('success', trans('admin/licenses/message.create.success'));
        }
        return redirect()->back()->withInput()->withErrors($license->getErrors());
    }

    /**
    * Returns a form with existing license data to allow an admin to
    * update license information.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $licenseId
    * @return \Illuminate\Contracts\View\View
     */
    public function edit($licenseId = null)
    {
        if (is_null($item = License::find($licenseId))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.does_not_exist'));
        }

        $this->authorize('update', $item);

        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No'
        ];

        return view('licenses/edit', compact('item'))
            ->with('depreciation_list', Helper::depreciationList())
            ->with('supplier_list', Helper::suppliersList())
            ->with('company_list', Helper::companyList())
            ->with('maintained_list', $maintained_list)
            ->with('manufacturer_list', Helper::manufacturerList());
    }


    /**
     * Validates and stores the license form data submitted from the edit
     * license form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getEdit() method that provides the form view
     * @since [v1.0]
     * @param Request $request
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $licenseId = null)
    {
        if (is_null($license = License::find($licenseId))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.does_not_exist'));
        }

        $this->authorize('update', $license);

        $license->company_id        = Company::getIdForCurrentUser($request->input('company_id'));
        $license->depreciation_id   = $request->input('depreciation_id');
        $license->expiration_date   = $request->input('expiration_date');
        $license->license_email     = $request->input('license_email');
        $license->license_name      = $request->input('license_name');
        $license->maintained        = $request->input('maintained',0);
        $license->name              = $request->input('name');
        $license->notes             = $request->input('notes');
        $license->order_number      = $request->input('order_number');
        $license->purchase_cost     = $request->input('purchase_cost');
        $license->purchase_date     = $request->input('purchase_date');
        $license->purchase_order    = $request->input('purchase_order');
        $license->reassignable      = $request->input('reassignable', 0);
        $license->serial            = $request->input('serial');
        $license->termination_date  = $request->input('termination_date');
        $license->seats             = e($request->input('seats'));
        $license->manufacturer_id   =  $request->input('manufacturer_id');

        if ($license->save()) {
            return redirect()->route('licenses.show', ['license' => $licenseId])->with('success', trans('admin/licenses/message.update.success'));
        }
        // If we can't adjust the number of seats, the error is flashed to the session by the event handler in License.php
        return redirect()->back()->withInput()->withErrors($license->getErrors());
    }

    /**
    * Checks to see whether the selected license can be deleted, and
    * if it can, marks it as deleted.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $licenseId
    * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($licenseId)
    {
        // Check if the license exists
        if (is_null($license = License::find($licenseId))) {
            // Redirect to the license management page
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        $this->authorize('delete', $license);

        if ($license->assigned_seats_count == 0) {
            // Delete the license and the associated license seats
            DB::table('license_seats')
                ->where('id', $license->id)
                ->update(array('assigned_to' => null,'asset_id' => null));

            $licenseSeats = $license->licenseseats();
            $licenseSeats->delete();
            $license->delete();

            // Redirect to the licenses management page
            return redirect()->route('licenses.index')->with('success', trans('admin/licenses/message.delete.success'));
            // Redirect to the license management page
        }
        // There are still licenses in use.
        return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.assoc_users'));

    }

    /**
    * Provides the form view for checking out a license to a user.
    * Here we pass the license seat ID instead of the license ID,
    * because licenses themselves are never checked out to anyone,
    * only the seats associated with them.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $seatId
    * @return \Illuminate\Contracts\View\View
     */
    public function getCheckout($seatId)
    {
        // Check if the license seat exists
        if (is_null($licenseSeat = LicenseSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        $this->authorize('checkout', $licenseSeat);
        return view('licenses/checkout', compact('licenseSeat'))
            ->with('users_list', Helper::usersList())
            ->with('asset_list', Helper::detailedAssetList());
    }


    /**
     * Validates and stores the license checkout action.
     *
     * @todo Switch to using a FormRequest for validation here.
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param Request $request
     * @param int $seatId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCheckout(Request $request, $seatId)
    {
        $licenseSeat = LicenseSeat::find($seatId);
        $assigned_to = e($request->input('assigned_to'));
        $asset_id = e($request->input('asset_id'));

        $this->authorize('checkout', $licenseSeat);

        // Declare the rules for the form validation
        $rules = [
            'note'   => 'string|nullable',
            'asset_id'  => 'required_without:assigned_to',
        ];

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return redirect()->back()->withInput()->withErrors($validator);
        }
        $target = null;
        if ($assigned_to!='') {
        // Check if the user exists
            if (is_null($target = User::find($assigned_to))) {
                // Redirect to the asset management page with error
                return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.user_does_not_exist'));
            }
        }

        if ($asset_id!='') {
            if (is_null($target = Asset::find($asset_id))) {
                // Redirect to the asset management page with error
                return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.asset_does_not_exist'));
            }

            if (($request->has('assigned_to')) && ($request->has('asset_id'))) {
                return redirect()->back()->withInput()->with('error', trans('admin/licenses/message.select_asset_or_person'));
            }
        }

        // Check if the asset exists
        if (is_null($licenseSeat)) {
            // Redirect to the asset management page with error
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        if ($request->input('asset_id') == '') {
            $licenseSeat->asset_id = null;
        } else {
            $licenseSeat->asset_id = $request->input('asset_id');
        }

        // Update the asset data
        if ($request->input('assigned_to') == '') {
                $licenseSeat->assigned_to =  null;
        } else {
                $licenseSeat->assigned_to = $request->input('assigned_to');
        }

        // Was the asset updated?
        if ($licenseSeat->save()) {
            $licenseSeat->logCheckout($request->input('note'), $target);

            $data['license_id'] = $licenseSeat->license_id;
            $data['note'] = $request->input('note');

            // Redirect to the new asset page
            return redirect()->route("licenses.index")->with('success', trans('admin/licenses/message.checkout.success'));
        }
        
        return redirect()->route("licenses.index")->with('error', trans('admin/licenses/message.checkout.error'));
    }


    /**
    * Makes the form view to check a license seat back into inventory.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $seatId
    * @param string $backTo
    * @return \Illuminate\Contracts\View\View
     */
    public function getCheckin($seatId = null, $backTo = null)
    {
        // Check if the asset exists
        if (is_null($licenseSeat = LicenseSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }
        $this->authorize('checkin', $licenseSeat);
        return view('licenses/checkin', compact('licenseSeat'))->with('backto', $backTo);
    }


    /**
    * Validates and stores the license checkin action.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see LicensesController::getCheckin() method that provides the form view
    * @since [v1.0]
    * @param int $seatId
    * @param string $backTo
    * @return \Illuminate\Http\RedirectResponse
     */
    public function postCheckin($seatId = null, $backTo = null)
    {
        // Check if the asset exists
        if (is_null($licenseSeat = LicenseSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        $license = License::find($licenseSeat->license_id);

        $this->authorize('checkin', $licenseSeat);

        if (!$license->reassignable) {
            // Not allowed to checkin
            Session::flash('error', 'License not reassignable.');
            return redirect()->back()->withInput();
        }

        // Declare the rules for the form validation
        $rules = array(
            'note'   => 'string',
            'notes'   => 'string',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return redirect()->back()->withInput()->withErrors($validator);
        }
        $return_to = User::find($licenseSeat->assigned_to);
        if (!$return_to) {
            $return_to = Asset::find($licenseSeat->asset_id);
        }
        // Update the asset data
        $licenseSeat->assigned_to                   = null;
        $licenseSeat->asset_id                      = null;

        // Was the asset updated?
        if ($licenseSeat->save()) {
            $licenseSeat->logCheckin($return_to, e(request('note')));
            if ($backTo=='user') {
                return redirect()->route("users.show", $return_to->id)->with('success', trans('admin/licenses/message.checkin.success'));
            }
            return redirect()->route("licenses.show", $licenseSeat->license_id)->with('success', trans('admin/licenses/message.checkin.success'));
        }

        // Redirect to the license page with error
        return redirect()->route("licenses.index")->with('error', trans('admin/licenses/message.checkin.error'));
    }

    /**
    * Makes the license detail page.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $licenseId
    * @return \Illuminate\Contracts\View\View
     */
    public function show($licenseId = null)
    {

        $license = License::find($licenseId);
        $license = $license->load('assignedusers', 'licenseSeats.user', 'licenseSeats.asset');

        if (isset($license->id)) {
            $license = $license->load('assignedusers', 'licenseSeats.user', 'licenseSeats.asset');
            $this->authorize('view', $license);
            return view('licenses/view', compact('license'));
        }
        $error = trans('admin/licenses/message.does_not_exist', compact('id'));
        return redirect()->route('licenses.index')->with('error', $error);
    }

    public function getClone($licenseId = null)
    {
        if (is_null($license_to_clone = License::find($licenseId))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.does_not_exist'));
        }

        $this->authorize('create', License::class);

        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No'
        ];
        //clone the orig
        $license = clone $license_to_clone;
        $license->id = null;
        $license->serial = null;

        // Show the page
        return view('licenses/edit')
        ->with('depreciation_list', Helper::depreciationList())
        ->with('supplier_list', Helper::suppliersList())
        ->with('item', $license)
        ->with('maintained_list', $maintained_list)
        ->with('company_list', Helper::companyList())
        ->with('manufacturer_list', Helper::manufacturerList());
    }


    /**
    * Validates and stores files associated with a license.
    *
    * @todo Switch to using the AssetFileRequest form request validator.
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $licenseId
    * @return \Illuminate\Http\RedirectResponse
     */
    public function postUpload(Request $request, $licenseId = null)
    {
        $license = License::find($licenseId);
        // the license is valid
        $destinationPath = config('app.private_uploads').'/licenses';

        if (isset($license->id)) {
            $this->authorize('update', $license);

            if (Input::hasFile('licensefile')) {

                foreach (Input::file('licensefile') as $file) {

                    $rules = array(
                    'licensefile' => 'required|mimes:png,gif,jpg,jpeg,doc,docx,pdf,txt,zip,rar,rtf,xml,lic|max:2000'
                    );
                    $validator = Validator::make(array('licensefile'=> $file), $rules);

                    if ($validator->fails()) {
                         return redirect()->back()->with('error', trans('admin/licenses/message.upload.invalidfiles'));
                    }
                    $extension = $file->getClientOriginalExtension();
                    $filename = 'license-'.$license->id.'-'.str_random(8);
                    $filename .= '-'.str_slug($file->getClientOriginalName()).'.'.$extension;
                    $upload_success = $file->move($destinationPath, $filename);

                    //Log the upload to the log
                    $license->logUpload($filename, e($request->input('notes')));
                }
                // This being called from a modal seems to confuse redirect()->back()
                // It thinks we should go to the dashboard.  As this is only used
                // from the modal at present, hardcode the redirect.  Longterm
                // maybe we evaluate something else.
                if ($upload_success) {
                    return redirect()->route('licenses.show', $license->id)->with('success', trans('admin/licenses/message.upload.success'));
                }
                return redirect()->route('licenses.show', $license->id)->with('error', trans('admin/licenses/message.upload.error'));
            }
            return redirect()->route('licenses.show', $license->id)->with('error', trans('admin/licenses/message.upload.nofiles'));
        }
        // Prepare the error message
        $error = trans('admin/licenses/message.does_not_exist', compact('id'));
        return redirect()->route('licenses.index')->with('error', $error);
    }


    /**
    * Deletes the selected license file.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $licenseId
    * @param int $fileId
    * @return \Illuminate\Http\RedirectResponse
     */
    public function getDeleteFile($licenseId = null, $fileId = null)
    {
        $license = License::find($licenseId);
        $destinationPath = config('app.private_uploads').'/licenses';

        // the license is valid
        if (isset($license->id)) {
            $this->authorize('edit', $license);
            $log = Actionlog::find($fileId);
            $full_filename = $destinationPath.'/'.$log->filename;
            if (file_exists($full_filename)) {
                unlink($destinationPath.'/'.$log->filename);
            }
            $log->delete();
            return redirect()->back()->with('success', trans('admin/licenses/message.deletefile.success'));
        }
        // Prepare the error message
        $error = trans('admin/licenses/message.does_not_exist', compact('id'));

        // Redirect to the licence management page
        return redirect()->route('licenses.index')->with('error', $error);
    }



    /**
    * Allows the selected file to be viewed.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.4]
    * @param int $licenseId
    * @param int $fileId
    * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function displayFile($licenseId = null, $fileId = null)
    {

        $license = License::find($licenseId);

        // the license is valid
        if (isset($license->id)) {
            $this->authorize('view', $license);
            $log = Actionlog::find($fileId);
            $file = $log->get_src('licenses');
            return Response::download($file);
        }
        // Prepare the error message
        $error = trans('admin/licenses/message.does_not_exist', compact('id'));
        // Redirect to the licence management page
        return redirect()->route('licenses.index')->with('error', $error);
    }


    /**
    * Generates a JSON response to populate the licence index datatables.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see LicensesController::getIndex() method that provides the view
    * @since [v1.0]
    * @return String JSON
    */
    public function getDatatable(Request $request)
    {
        $this->authorize('view', License::class);
        $licenses = Company::scopeCompanyables(License::with('company', 'licenseSeatsRelation', 'manufacturer'));

        if (Input::has('search')) {
            $licenses = $licenses->TextSearch($request->input('search'));
        }
        $offset = request('offset', 0);
        $limit = request('limit', 50);

        $allowed_columns = ['id','name','purchase_cost','expiration_date','purchase_order','order_number','notes','purchase_date','serial','manufacturer','company'];
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'created_at';

        switch ($sort) {
            case 'manufacturer':
                $licenses = $licenses->OrderManufacturer($order);
                break;
            case 'company':
                $licenses = $licenses->OrderCompany($order);
                break;
            default:
                $licenses = $licenses->orderBy($sort, $order);
                break;
        }

        $licenseCount = $licenses->count();
        $licenses = $licenses->skip($offset)->take($limit)->get();

        $rows = array();

        foreach ($licenses as $license) {
            $rows[] = $license->present()->forDataTable();
        }

        $data = array('total' => $licenseCount, 'rows' => $rows);

        return $data;
    }

    /**
    * Generates the next free seat ID for checkout.
    *
    * @todo This is a dumb way to solve this problem.
    * Author should refactor. And go hide in a hole and
    * think about what she's done. And perhaps find a new
    * line of work. And get in the sea.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @param int $licenseId
    * @return \Illuminate\Http\RedirectResponse
     */
    public function getFreeLicense($licenseId)
    {
        $this->authorize('checkout', License::class);
        if (is_null($license = License::find($licenseId))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }
        $seatId = $license->freeSeat($licenseId);
        return redirect()->route('licenses.checkout', $seatId);
    }
}
