<?php

use Yannelli\LaravelPlaud\Constants\FileTypes;

describe('FileTypes', function () {
    it('has MP3 constant', function () {
        expect(FileTypes::MP3)->toBe('MP3');
    });

    it('has WAV constant', function () {
        expect(FileTypes::WAV)->toBe('WAV');
    });

    it('has TXT constant', function () {
        expect(FileTypes::TXT)->toBe('TXT');
    });

    it('has PDF constant', function () {
        expect(FileTypes::PDF)->toBe('PDF');
    });

    it('has DOCX constant', function () {
        expect(FileTypes::DOCX)->toBe('DOCX');
    });

    it('has SRT constant', function () {
        expect(FileTypes::SRT)->toBe('SRT');
    });

    it('has MARKDOWN constant', function () {
        expect(FileTypes::MARKDOWN)->toBe('markdown');
    });

    it('has all expected audio file types', function () {
        $audioTypes = [FileTypes::MP3, FileTypes::WAV];

        expect($audioTypes)->toContain('MP3')
            ->and($audioTypes)->toContain('WAV');
    });

    it('has all expected document file types', function () {
        $docTypes = [FileTypes::TXT, FileTypes::PDF, FileTypes::DOCX, FileTypes::SRT, FileTypes::MARKDOWN];

        expect($docTypes)->toHaveCount(5);
    });
});
