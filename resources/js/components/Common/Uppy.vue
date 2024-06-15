<template>
    <div id="uppy-container"></div>
</template>

<script>
    import Uppy from '@uppy/core';
    import Dashboard from '@uppy/dashboard';
    import XHRUpload from '@uppy/xhr-upload';
    import ImageEditor from '@uppy/image-editor';
    import Compressor from '@uppy/compressor';
    import moment from 'moment';
    import Request from '@/mixins/Request';

    import '@uppy/core/dist/style.css';
    import '@uppy/dashboard/dist/style.css';
    import '@uppy/image-editor/dist/style.min.css';

    export default {
        mixins: [
            Request,
        ],
        props: {
            id: {
                type: String,
            },
            autoProceed: {
                type: Boolean,
                default: false,
            },
            allowMultipleUploadBatches: {
                type: Boolean,
                default: true,
            },
            debug: {
                type: Boolean,
                default: false,
            },
            url: {
                type: String,
                required: true,
            },
            maxFileSize: {
                type: [Number, null],
                default: 10000000, // 10MB
            },
            minFileSize: {
                type: [Number, null],
                default: null,
            },
            maxTotalFileSize: {
                type: [Number, null],
                default: 100000000, // 100MB,
            },
            maxNumberOfFiles: {
                type: [Number, null],
                default: 25,
            },
            minNumberOfFiles: {
                type: [Number, null],
                default: null,
            },
            allowedFileTypes: {
                type: [Array, null],
                default() {
                    return ['image/*', 'video/*'];
                },
            },
            requiredMetaFields: {
                type: Array,
                default() {
                    return ['name'];
                },
            },
            globalMetaData: {
                type: Object,
                default() {
                    return {};
                },
            },
            onBeforeFileAdded: {
                type: Function,
                default(currentFile, files) {
                    if (!currentFile.type) {
                        this.uploader.info(`Skipping file because it has no type`, 'error', 500);

                        return false;
                    }
                },
            },
            onBeforeUpload: {
                type: Function,
                default (files) {
                    return true;
                },
            },
            logger: {
                type: Object,
                default() {
                    const timeStamp = moment(+new Date()).format('DD.MM.YYYY HH:mm');

                    return {
                        debug: (...args) => console.debug(`[Uppy] [${timeStamp}]`, ...args),
                        warn: (...args) => console.warn(`[Uppy] [${timeStamp}]`, ...args),
                        error: (...args) => console.error(`[Uppy] [${timeStamp}]`, ...args),
                    };
                },
            },
            infoTimeout: {
                type: Number,
                default: 5000,
            },
            metaFields: {
                type: Array,
                default() {
                    return [
                        { id: 'name', name: 'Name', placeholder: 'Enter name' },
                    ];
                },
            },
            width: {
                type: Number,
                default: 750,
            },
            height: {
                type: Number,
                default: 400,
            },
            thumbnailWidth: {
                type: Number,
                default: 280,
            },
            note: {
                type: String,
                default() {
                    return 'Allowed file types - pictures, videos. Maximum file size - 10MB. Maximum number of files - 25. Maximum size of all files - 100MB.';
                },
            },
            additionalPostData: {
                type: Object,
                default() {
                    return {};
                },
            },
            itemType: {
                type: String,
                default: 'photo',
            },
            deletePath: {
                type: String,
                default: 'uploads/images',
            },
            deleteDisk: {
                type: String,
                default: 'public',
            },
            showNote: {
                type: Boolean,
                default: true,
            },
        },
        data() {
            return {
                uploader: null,
            };
        },
        mounted() {
            this.setUp();
        },
        methods: {
            setUp() {
                const locale = 'en';

                this.uploader = new Uppy({
                    id: this.id,
                    autoProceed: this.autoProceed,
                    allowMultipleUploadBatches: this.allowMultipleUploadBatches,
                    debug: this.debug,
                    restrictions: {
                        maxFileSize: this.maxFileSize,
                        minFileSize: this.minFileSize,
                        maxTotalFileSize: this.maxTotalFileSize,
                        maxNumberOfFiles: this.maxNumberOfFiles,
                        minNumberOfFiles: this.minNumberOfFiles,
                        allowedFileTypes: this.allowedFileTypes,
                        requiredMetaFields: this.requiredMetaFields,
                    },
                    meta: this.globalMetaData,
                    onBeforeFileAdded: this.onBeforeFileAdded,
                    onBeforeUpload: this.onBeforeUpload,
                    locale: {
                        strings: this.getTranslations(locale),
                    },
                    // logger: this.logger,
                    infoTimeout: this.infoTimeout,
                })
                .use(Dashboard, {
                    target: this.$el,
                    inline: true,
                    showProgressDetails: true,
                    showRemoveButtonAfterComplete: true,
                    replaceTargetContent: true,
                    locale: {
                        strings: this.getTranslations(locale),
                    },
                    metaFields: this.metaFields,
                    width: this.width,
                    height: this.height,
                    thumbnailWidth: this.thumbnailWidth,
                    note: this.showNote ? this.note : '',
                    proudlyDisplayPoweredByUppy: false,
                    showProgressDetails: true,
                    doneButtonHandler: null,
                    // debug: true,
                })
                .use(Compressor)
                .use(XHRUpload, {
                    endpoint: this.url,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    locale: {
                        strings: this.getTranslations(locale),
                    },
                })
                .use(ImageEditor, { 
                    target: Dashboard,
                    locale: {
                        strings: this.getTranslations(locale),
                    },
                });

                if (Object.keys(this.additionalPostData).length) {
                    this.uploader.setMeta(this.additionalPostData);
                }

                this.setUpEventListeners();
            },
            setUpEventListeners() {
                this.uploader.on('file-added', (file) => {
                    this.uploader.setFileMeta(file.id, {
                        size: file.size,
                        uploaded: false,
                    });
                });

                this.uploader.on('progress', (progress) => {
                    this.$emit('upload-in-progress', progress);
                });

                this.uploader.on('cancel-all', () => {
                    this.$emit('all-uploads-cancelled');
                });

                this.uploader.on('upload-success', (file, {status, body, uploadURL}) => {
                    this.uploader.setFileMeta(file.id, {
                        uploaded: true,
                    });

                    let emittableData;

                    switch (this.itemType) {
                        case 'photo': {
                            const photoPath = body.data;
                            emittableData = {...file.meta, photoPath};
                            break;
                        }
                        default:
                            emittableData = {...file.meta};
                    }

                    this.$emit('file-uploaded', emittableData);
                });

                this.uploader.on('upload-error', (file, error, response) => {
                    if (response.status === 500) {
                        const errorMsg = response.body.errors;
                        const errorMsgToDisplay = `File ${file.name} failed to upload, because: ${errorMsg}`;

                        this.$bvToast.toast(errorMsgToDisplay, {
                            title: 'Upload failed',
                            variant: 'danger',
                        });
                    }
                });

                this.uploader.on('file-removed', (file, reason) => {
                    if (reason === 'removed-by-user' && file.meta.uploaded) {
                        this.deleteItem(file.meta.name);
                    }
                });
            },
            async deleteItem(name) {
                const params = {
                    path: this.deletePath,
                    disk: this.deleteDisk,
                };
                
                const paramString = jQuery.param(params);

                await this.request('DELETE', `/files/${encodeURIComponent(name)}?${paramString}`);

                this.$bvToast.toast('Photo deleted', {
                    title: 'Action successfully completed',
                    variant: 'success',
                });

                this.$emit('file-removed', name);
            },
            getTranslations(locale) {
                const translations = {
                    en: {
                        addBulkFilesFailed: {
                            '0': 'Failed to add %{smart_count} file due to an internal error',
                            '1': 'Failed to add %{smart_count} files due to internal errors',
                        },
                        addingMoreFiles: 'Adding more files',
                        addMore: 'Add more',
                        addMoreFiles: 'Add more files',
                        allFilesFromFolderNamed: 'All files from folder %{name}',
                        allowAccessDescription: 'In order to take pictures or record video with your camera, please allow camera access for this site.',
                        allowAccessTitle: 'Please allow access to your camera',
                        allowAudioAccessDescription: 'In order to record audio, please allow microphone access for this site.',
                        allowAudioAccessTitle: 'Please allow access to your microphone',
                        aspectRatioLandscape: 'Crop landscape (16:9)',
                        aspectRatioPortrait: 'Crop portrait (9:16)',
                        aspectRatioSquare: 'Crop square',
                        authAborted: 'Authentication aborted',
                        authenticateWith: 'Connect to %{pluginName}',
                        authenticateWithTitle: 'Please authenticate with %{pluginName} to select files',
                        back: 'Back',
                        browse: 'browse',
                        browseFiles: 'browse files',
                        browseFolders: 'browse folders',
                        cancel: 'Cancel',
                        cancelUpload: 'Cancel upload',
                        chooseFiles: 'Choose files',
                        closeModal: 'Close Modal',
                        companionError: 'Connection with Companion failed',
                        companionUnauthorizeHint: 'To unauthorize to your %{provider} account, please go to %{url}',
                        complete: 'Complete',
                        compressedX: 'Saved %{size} by compressing images',
                        compressingImages: 'Compressing images...',
                        connectedToInternet: 'Connected to the Internet',
                        copyLink: 'Copy link',
                        copyLinkToClipboardFallback: 'Copy the URL below',
                        copyLinkToClipboardSuccess: 'Link copied to clipboard.',
                        creatingAssembly: 'Preparing upload...',
                        creatingAssemblyFailed: 'Transloadit: Could not create Assembly',
                        dashboardTitle: 'Uppy Dashboard',
                        dashboardWindowTitle: 'Uppy Dashboard Window (Press escape to close)',
                        dataUploadedOfTotal: '%{complete} of %{total}',
                        discardRecordedFile: 'Discard recorded file',
                        done: 'Done',
                        dropHereOr: 'Drop here or %{browse}',
                        dropHint: 'Drop your files here',
                        dropPasteBoth: 'Drop files here, %{browseFiles} or %{browseFolders}',
                        dropPasteFiles: 'Drop files here or %{browseFiles}',
                        dropPasteFolders: 'Drop files here or %{browseFolders}',
                        dropPasteImportBoth: 'Drop files here, %{browseFiles}, %{browseFolders} or import from:',
                        dropPasteImportFiles: 'Drop files here, %{browseFiles} or import from:',
                        dropPasteImportFolders: 'Drop files here, %{browseFolders} or import from:',
                        editFile: 'Edit file',
                        editFileWithFilename: 'Edit file %{file}',
                        editing: 'Editing %{file}',
                        emptyFolderAdded: 'No files were added from empty folder',
                        encoding: 'Encoding...',
                        enterCorrectUrl: 'Incorrect URL: Please make sure you are entering a direct link to a file',
                        enterTextToSearch: 'Enter text to search for images',
                        enterUrlToImport: 'Enter URL to import a file',
                        exceedsSize: '%{file} exceeds maximum allowed size of %{size}',
                        failedToFetch: 'Companion failed to fetch this URL, please make sure it’s correct',
                        failedToUpload: 'Failed to upload %{file}',
                        filesUploadedOfTotal: {
                            '0': '%{complete} of %{smart_count} file uploaded',
                            '1': '%{complete} of %{smart_count} files uploaded',
                        },
                        filter: 'Filter',
                        finishEditingFile: 'Finish editing file',
                        flipHorizontal: 'Flip horizontal',
                        folderAdded: {
                            '0': 'Added %{smart_count} file from %{folder}',
                            '1': 'Added %{smart_count} files from %{folder}',
                        },
                        folderAlreadyAdded: 'The folder "%{folder}" was already added',
                        generatingThumbnails: 'Generating thumbnails...',
                        import: 'Import',
                        importFiles: 'Import files from:',
                        importFrom: 'Import from %{name}',
                        inferiorSize: 'This file is smaller than the allowed size of %{size}',
                        loading: 'Loading...',
                        logOut: 'Log out',
                        micDisabled: 'Microphone access denied by user',
                        missingRequiredMetaField: 'Missing required meta fields',
                        missingRequiredMetaFieldOnFile: 'Missing required meta fields in %{fileName}',
                        missingRequiredMetaFields: {
                            '0': 'Missing required meta field: %{fields}.',
                            '1': 'Missing required meta fields: %{fields}.',
                        },
                        myDevice: 'My Device',
                        noAudioDescription: 'In order to record audio, please connect a microphone or another audio input device',
                        noAudioTitle: 'Microphone Not Available',
                        noCameraDescription: 'In order to take pictures or record video, please connect a camera device',
                        noCameraTitle: 'Camera Not Available',
                        noDuplicates: "Cannot add the duplicate file '%{fileName}', it already exists",
                        noFilesFound: 'You have no files or folders here',
                        noInternetConnection: 'No Internet connection',
                        noMoreFilesAllowed: 'Cannot add more files',
                        openFolderNamed: 'Open folder %{name}',
                        pause: 'Pause',
                        paused: 'Paused',
                        pauseUpload: 'Pause upload',
                        pluginNameAudio: 'Audio',
                        pluginNameBox: 'Box',
                        pluginNameCamera: 'Camera',
                        pluginNameDropbox: 'Dropbox',
                        pluginNameFacebook: 'Facebook',
                        pluginNameGoogleDrive: 'Google Drive',
                        pluginNameInstagram: 'Instagram',
                        pluginNameOneDrive: 'OneDrive',
                        pluginNameZoom: 'Zoom',
                        poweredBy: 'Powered by %{uppy}',
                        processingXFiles: {
                            '0': 'Processing %{smart_count} file',
                            '1': 'Processing %{smart_count} files',
                        },
                        recording: 'Recording',
                        recordingLength: 'Recording length %{recording_length}',
                        recordingStoppedMaxSize: 'Recording stopped because the file size is about to exceed the limit',
                        recordVideoBtn: 'Record Video',
                        recoveredAllFiles: 'We restored all files. You can now resume the upload.',
                        recoveredXFiles: {
                            '0': 'We could not fully recover 1 file. Please re-select it and resume the upload.',
                            '1': 'We could not fully recover %{smart_count} files. Please re-select them and resume the upload.',
                        },
                        removeFile: 'Remove file',
                        reSelect: 'Re-select',
                        resetFilter: 'Reset filter',
                        resume: 'Resume',
                        resumeUpload: 'Resume upload',
                        retry: 'Retry',
                        retryUpload: 'Retry upload',
                        revert: 'Revert',
                        rotate: 'Rotate',
                        save: 'Save',
                        saveChanges: 'Save changes',
                        search: 'Search',
                        searchImages: 'Search for images',
                        selectX: {
                            '0': 'Select %{smart_count}',
                            '1': 'Select %{smart_count}',
                        },
                        sessionRestored: 'Session restored',
                        showErrorDetails: 'Show error details',
                        signInWithGoogle: 'Sign in with Google',
                        smile: 'Smile!',
                        startAudioRecording: 'Begin audio recording',
                        startCapturing: 'Begin screen capturing',
                        startRecording: 'Begin video recording',
                        stopAudioRecording: 'Stop audio recording',
                        stopCapturing: 'Stop screen capturing',
                        stopRecording: 'Stop video recording',
                        streamActive: 'Stream active',
                        streamPassive: 'Stream passive',
                        submitRecordedFile: 'Submit recorded file',
                        takePicture: 'Take a picture',
                        takePictureBtn: 'Take Picture',
                        timedOut: 'Upload stalled for %{seconds} seconds, aborting.',
                        upload: 'Upload',
                        uploadComplete: 'Upload complete',
                        uploadFailed: 'Upload failed',
                        uploading: 'Uploading',
                        uploadingXFiles: {
                            '0': 'Uploading %{smart_count} file',
                            '1': 'Uploading %{smart_count} files',
                        },
                        uploadPaused: 'Upload paused',
                        uploadXFiles: {
                            '0': 'Upload %{smart_count} file',
                            '1': 'Upload %{smart_count} files',
                        },
                        uploadXNewFiles: {
                            '0': 'Upload +%{smart_count} file',
                            '1': 'Upload +%{smart_count} files',
                        },
                        xFilesSelected: {
                            '0': '%{smart_count} file selected',
                            '1': '%{smart_count} files selected',
                        },
                        xMoreFilesAdded: {
                            '0': '%{smart_count} more file added',
                            '1': '%{smart_count} more files added',
                        },
                        xTimeLeft: '%{time} left',
                        youCanOnlyUploadFileTypes: 'You can only upload: %{types}',
                        youCanOnlyUploadX: {
                            '0': 'You can only upload %{smart_count} file',
                            '1': 'You can only upload %{smart_count} files',
                        },
                        youHaveToAtLeastSelectX: {
                            '0': 'You have to select at least %{smart_count} file',
                            '1': 'You have to select at least %{smart_count} files',
                        },
                        zoomIn: 'Zoom in',
                        zoomOut: 'Zoom out',
                    },
                    lv: {
                        addBulkFilesFailed: {
                            '0': 'Failed to add %{smart_count} file due to an internal error',
                            '1': 'Failed to add %{smart_count} files due to internal errors',
                        },
                        addingMoreFiles: 'Notiek failu pievienošana',
                        addMore: 'Pievienot vēl failus',
                        addMoreFiles: 'Pievienot failus',
                        allFilesFromFolderNamed: 'All files from folder %{name}',
                        allowAccessDescription: 'In order to take pictures or record video with your camera, please allow camera access for this site.',
                        allowAccessTitle: 'Please allow access to your camera',
                        allowAudioAccessDescription: 'In order to record audio, please allow microphone access for this site.',
                        allowAudioAccessTitle: 'Please allow access to your microphone',
                        aspectRatioLandscape: 'Crop landscape (16:9)',
                        aspectRatioPortrait: 'Crop portrait (9:16)',
                        aspectRatioSquare: 'Crop square',
                        authAborted: 'Authentication aborted',
                        authenticateWith: 'Connect to %{pluginName}',
                        authenticateWithTitle: 'Please authenticate with %{pluginName} to select files',
                        back: 'Atpakaļ',
                        browse: 'Pārlūkot',
                        browseFiles: 'Pārlūkot failus',
                        browseFolders: 'Pārlūkot mapes',
                        cancel: 'Atcelt',
                        cancelUpload: 'Atcelt augšupielādi',
                        chooseFiles: 'Choose files',
                        closeModal: 'Aizvērt logu',
                        companionError: 'Connection with Companion failed',
                        companionUnauthorizeHint: 'To unauthorize to your %{provider} account, please go to %{url}',
                        complete: 'Pabeigts',
                        compressedX: 'Saved %{size} by compressing images',
                        compressingImages: 'Compressing images...',
                        connectedToInternet: 'Connected to the Internet',
                        copyLink: 'Kopēt saiti',
                        copyLinkToClipboardFallback: 'Nokopējiet zemāk redzamo saiti',
                        copyLinkToClipboardSuccess: 'Saite nokopēta',
                        creatingAssembly: 'Preparing upload...',
                        creatingAssemblyFailed: 'Transloadit: Could not create Assembly',
                        dashboardTitle: 'Failu augšupielādes panelis',
                        dashboardWindowTitle: 'Failu augšupielādes paneļa logs',
                        dataUploadedOfTotal: '%{complete} no %{total}',
                        discardRecordedFile: 'Discard recorded file',
                        done: 'Gatavs',
                        dropHereOr: 'Drop here or %{browse}',
                        dropHint: 'Velciet failus šeit',
                        dropPasteBoth: 'Velciet failus šeit, iekopējiet vai pārlūkojiet',
                        dropPasteFiles: 'Velciet failus šeit vai pārlūkojiet tos',
                        dropPasteFolders: 'Drop files here or %{browseFolders}',
                        dropPasteImportBoth: 'Drop files here, %{browseFiles}, %{browseFolders} or import from:',
                        dropPasteImportFiles: 'Drop files here, %{browseFiles} or import from:',
                        dropPasteImportFolders: 'Drop files here, %{browseFolders} or import from:',
                        editFile: 'Labot failu',
                        editFileWithFilename: 'Labot failu %{file}',
                        editing: 'Notiek faila %{file} labošana',
                        emptyFolderAdded: 'No files were added from empty folder',
                        encoding: 'Encoding...',
                        enterCorrectUrl: 'Incorrect URL: Please make sure you are entering a direct link to a file',
                        enterTextToSearch: 'Enter text to search for images',
                        enterUrlToImport: 'Enter URL to import a file',
                        exceedsSize: '%{file} exceeds maximum allowed size of %{size}',
                        failedToFetch: 'Companion failed to fetch this URL, please make sure it’s correct',
                        failedToUpload: 'Neizdevās augšupielādēt failu %{file}',
                        filesUploadedOfTotal: {
                            '0': '%{complete} no %{smart_count} failiem augšupielādēts',
                            '1': '%{complete} no %{smart_count} failiem augšupielādēts',
                        },
                        filter: 'Filter',
                        finishEditingFile: 'Beigt faila labošanu',
                        flipHorizontal: 'Apgriezt horizontāli',
                        folderAdded: {
                            '0': 'Added %{smart_count} file from %{folder}',
                            '1': 'Added %{smart_count} files from %{folder}',
                        },
                        folderAlreadyAdded: 'The folder "%{folder}" was already added',
                        generatingThumbnails: 'Generating thumbnails...',
                        import: 'Import',
                        importFiles: 'Importēt failus no:',
                        importFrom: 'Importēt no %{name}',
                        inferiorSize: 'This file is smaller than the allowed size of %{size}',
                        loading: 'Loading...',
                        logOut: 'Log out',
                        micDisabled: 'Microphone access denied by user',
                        missingRequiredMetaField: 'Missing required meta fields',
                        missingRequiredMetaFieldOnFile: 'Missing required meta fields in %{fileName}',
                        missingRequiredMetaFields: {
                            '0': 'Missing required meta field: %{fields}.',
                            '1': 'Missing required meta fields: %{fields}.',
                        },
                        myDevice: 'Mana ierīce',
                        noAudioDescription: 'In order to record audio, please connect a microphone or another audio input device',
                        noAudioTitle: 'Microphone Not Available',
                        noCameraDescription: 'In order to take pictures or record video, please connect a camera device',
                        noCameraTitle: 'Camera Not Available',
                        noDuplicates: "Nevar pievienot failu '%{fileName}', tas jau ir ticis pievienots",
                        noFilesFound: 'You have no files or folders here',
                        noInternetConnection: 'No Internet connection',
                        noMoreFilesAllowed: 'Maksimālais pievienojamo failu skaits ir sasniegts',
                        openFolderNamed: 'Open folder %{name}',
                        pause: 'Pause',
                        paused: 'Paused',
                        pauseUpload: 'Pauzēt augšupielādi',
                        pluginNameAudio: 'Audio',
                        pluginNameBox: 'Box',
                        pluginNameCamera: 'Camera',
                        pluginNameDropbox: 'Dropbox',
                        pluginNameFacebook: 'Facebook',
                        pluginNameGoogleDrive: 'Google Drive',
                        pluginNameInstagram: 'Instagram',
                        pluginNameOneDrive: 'OneDrive',
                        pluginNameZoom: 'Zoom',
                        poweredBy: 'Powered by %{uppy}',
                        processingXFiles: {
                            '0': 'Notiek %{smart_count} faila apstrāde',
                            '1': 'Notiek %{smart_count} failu apstrāde',
                        },
                        recording: 'Recording',
                        recordingLength: 'Recording length %{recording_length}',
                        recordingStoppedMaxSize: 'Recording stopped because the file size is about to exceed the limit',
                        recordVideoBtn: 'Uzņemt video',
                        recoveredAllFiles: 'We restored all files. You can now resume the upload.',
                        recoveredXFiles: {
                            '0': 'We could not fully recover 1 file. Please re-select it and resume the upload.',
                            '1': 'We could not fully recover %{smart_count} files. Please re-select them and resume the upload.',
                        },
                        removeFile: 'Noņemt failu',
                        reSelect: 'Re-select',
                        resetFilter: 'Reset filter',
                        resume: 'Resume',
                        resumeUpload: 'Atsākt augšupielādi',
                        retry: 'Mēģināt vēlreiz',
                        retryUpload: 'Mēģināt vēlreiz',
                        revert: 'Atcelt',
                        rotate: 'Pagriezt',
                        save: 'Saglabāt',
                        saveChanges: 'Saglabāt izmaiņas',
                        search: 'Search',
                        searchImages: 'Search for images',
                        selectX: {
                            '0': 'Select %{smart_count}',
                            '1': 'Select %{smart_count}',
                        },
                        sessionRestored: 'Sesija atjaunota',
                        showErrorDetails: 'Show error details',
                        signInWithGoogle: 'Sign in with Google',
                        smile: 'Smile!',
                        startAudioRecording: 'Begin audio recording',
                        startCapturing: 'Begin screen capturing',
                        startRecording: 'Begin video recording',
                        stopAudioRecording: 'Stop audio recording',
                        stopCapturing: 'Stop screen capturing',
                        stopRecording: 'Stop video recording',
                        streamActive: 'Stream active',
                        streamPassive: 'Stream passive',
                        submitRecordedFile: 'Submit recorded file',
                        takePicture: 'Take a picture',
                        takePictureBtn: 'Uzņemt bildi',
                        timedOut: 'Upload stalled for %{seconds} seconds, aborting.',
                        upload: 'Upload',
                        uploadComplete: 'Augšupielāde pabeigta',
                        uploadFailed: 'Augšupielāde neizdevās',
                        uploading: 'Notiek augšupielāde',
                        uploadingXFiles: {
                            '0': 'Notiek %{smart_count} faila augšupielāde',
                            '1': 'Notiek %{smart_count} failu augšupielāde',
                        },
                        uploadPaused: 'Augšupielāde pauzēta',
                        uploadXFiles: {
                            '0': 'Augšupielādēt %{smart_count} failu',
                            '1': 'Augšupielādēt %{smart_count} failus',
                        },
                        uploadXNewFiles: {
                            '0': 'Augšupielādēt vēl %{smart_count} failu',
                            '1': 'Augšupielādēt vēl %{smart_count} failus',
                        },
                        xFilesSelected: {
                            '0': '%{smart_count} fails izvēlēts',
                            '1': '%{smart_count} faili izvēlēti',
                        },
                        xMoreFilesAdded: {
                            '0': '%{smart_count} more file added',
                            '1': '%{smart_count} more files added',
                        },
                        xTimeLeft: 'atlikušais laiks %{time}',
                        youCanOnlyUploadFileTypes: 'You can only upload: %{types}',
                        youCanOnlyUploadX: {
                            '0': 'Jūs variet augšupielādēt ne vairāk kā %{smart_count} failu',
                            '1': 'Jūs variet augšupielādēt ne vairāk kā %{smart_count} failus',
                        },
                        youHaveToAtLeastSelectX: {
                            '0': 'You have to select at least %{smart_count} file',
                            '1': 'You have to select at least %{smart_count} files',
                        },
                        zoomIn: 'Palielināt',
                        zoomOut: 'Attālināt',
                    },
                };

                return translations[locale] ?? {};
            },
        },
    };
</script>
