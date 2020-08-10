/* External dependencies */
import { withDispatch, withSelect } from "@wordpress/data";
import { compose } from "@wordpress/compose";

/* Internal dependencies */
import RelatedKeyphrasesModalContent from "../components/RelatedKeyphrasesModalContent";

export default compose( [
	withSelect( ( select ) => {
		return {
			keyphrase: select( "yoast-seo/editor" ).getFocusKeyphrase(),
			countryCode: select( "yoast-seo/editor" ).getSEMrushSelectedCountry(),
			OAuthToken: select( "yoast-seo/editor" ).getSEMrushRequestOAuthToken(),
			requestLimitReached: select( "yoast-seo/editor" ).getSEMrushRequestLimitReached(),
			response: select( "yoast-seo/editor" ).getSEMrushRequestResponse(),
			isSuccess: select( "yoast-seo/editor" ).getSEMrushRequestIsSuccess(),
			isPending: select( "yoast-seo/editor" ).getSEMrushIsRequestPending(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const {
			setSEMrushChangeCountry,
			setSEMrushNewRequest,
			setSEMrushRequestSucceeded,
			setSEMrushRequestFailed,
			setSEMrushSetRequestLimitReached,
		 } = dispatch( "yoast-seo/editor" );
		return {
			setCountry: ( countryCode ) => {
				setSEMrushChangeCountry( countryCode );
			},
			newRequest: ( countryCode, keyphrase, OAuthToken ) => {
				setSEMrushNewRequest( countryCode, keyphrase, OAuthToken );
			},
			setRequestSucceeded: ( response ) => {
				setSEMrushRequestSucceeded( response );
			},
			setRequestFailed: ( response ) => {
				setSEMrushRequestFailed( response );
			},
			setRequestLimitReached: () => {
				setSEMrushSetRequestLimitReached();
			},
		};
	} ),
] )( RelatedKeyphrasesModalContent );
