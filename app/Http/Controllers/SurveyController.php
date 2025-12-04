<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    public function saveSurvey(Request $request)
    {
        try {
            DB::table('PartnershipSurvey')->insert([
                'partnerName'            => $request->partnerName,
                'country'                => $request->country,
                'address'                => $request->address,
                'contactName'            => $request->contactName,
                'contactNumber'          => $request->contactNumber,
                'email'                  => $request->email,
                'website'                => $request->website,

                'partnershipType'        => $request->partnershipType,
                'otherPartnershipType'   => $request->otherPartnershipType,

                'aciDepartment'          => $request->aciDepartment,
                'initialDate'            => $request->initialDate ?: null,
                'initialValue'           => $request->initialValue,
                'latestDate'             => $request->latestDate ?: null,
                'latestValue'            => $request->latestValue,

                'aciContactName'         => $request->aciContactName,
                'aciMobile'              => $request->aciMobile,
                'aciEmail'               => $request->aciEmail,

                'whyNeeds'               => $request->whyNeeds,
                'whyObjective'           => $request->whyObjective,
                'whatCapabilities'       => $request->whatCapabilities,
                'whatAdvantage'          => $request->whatAdvantage,
                'whatFocus'              => $request->whatFocus,
                'howGovernance'          => $request->howGovernance,
                'howActions'             => $request->howActions,
                'howFinance'             => $request->howFinance,

                'futureEvolution'        => $request->futureEvolution,
                'futureRisks'            => $request->futureRisks,
                'futureAdaptation'       => $request->futureAdaptation,
                'additionalComments'     => $request->additionalComments,

                'submittedBy'            => $request->submittedBy,
                'submitterEmail'         => $request->submitterEmail,
                'submitterContact'       => $request->submitterContact,

                'created_at'             => now(),
            ]);

            return response()->json([
                'message' => 'Survey data saved successfully.'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'An error occurred during submission.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
