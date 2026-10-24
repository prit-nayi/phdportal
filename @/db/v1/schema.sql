DROP DATABASE IF EXISTS `phd_db`;
CREATE DATABASE `phd_db`;
USE `phd_db`;

-- ====================
-- Basics
-- ====================

CREATE TABLE `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    
    `gmail` VARCHAR(255) NOT NULL,
    `isActive` BOOLEAN NOT NULL DEFAULT TRUE,

    `role` ENUM('Admin', 'Student') NOT NULL DEFAULT 'Student',

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `admins` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    
    `name` VARCHAR(255) NOT NULL,
    
    `userId` INT NOT NULL,
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);

CREATE TABLE `phdBatches` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    
    `name` VARCHAR(255) NOT NULL,
    `feeAmount` DECIMAL(10, 2) NOT NULL,
    `enrollmentYear` INT NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);

-- Note: single entry via form and bulk entry via csv upload
CREATE TABLE `phdStudents` ( 
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `batchId` INT NOT NULL,
    FOREIGN KEY (`batchId`) REFERENCES `phdBatches`(`Id`),

    `userId` INT NOT NULL,
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`),

    `Graduate` TEXT,
    `ApplicationNo` TEXT NOT NULL,
    `Status` TEXT NOT NULL,
    `ProgramName` TEXT NOT NULL,
    `InstituteName` TEXT,
    `StudentName` TEXT NOT NULL,
    `Surname` TEXT NOT NULL,
    `FathersName` TEXT NOT NULL,
    `MothersName` TEXT NOT NULL,
    `PANNoParent` TEXT,
    `PANNoStudent` TEXT,
    `AadharNoParent` TEXT,
    `AadharNoStudent` TEXT,
    `NameofLocalGuardian` TEXT,
    `PermanentAddress` TEXT NOT NULL,
    `PermanentCity` TEXT NOT NULL,
    `PermanentPinCode` TEXT NOT NULL,
    `LocalAddress` TEXT NOT NULL,
    `LocalCity` TEXT NOT NULL,
    `LocalPinCode` TEXT NOT NULL,
    `LocalPhone` TEXT NOT NULL,
    `MobileStudent` TEXT NOT NULL,
    `MobileParent` TEXT NOT NULL,
    `MobileGuardian` TEXT,
    `EmailStudent` TEXT NOT NULL,
    `EmailParent` TEXT,
    `EmailGuardian` TEXT,
    `BirthDateStudent` TEXT NOT NULL,
    `Religion` TEXT NOT NULL,
    `Minority` TEXT NOT NULL,
    `Caste` TEXT NOT NULL,
    `BloodGroup` TEXT,
    `Height_inCms` TEXT,
    `Weight_inKg` TEXT,
    `Category` TEXT NOT NULL,
    `Sex` TEXT NOT NULL,
    `SSC_Month_Year` TEXT NOT NULL,
    `SSC_BoardName` TEXT NOT NULL,
    `SSC_TotalMarks` TEXT NOT NULL,
    `SSC_ObtainedMarks` TEXT NOT NULL,
    `SSC_Percentage` TEXT NOT NULL,
    `HSC_Month_Year` TEXT NOT NULL,
    `HSC_BoardName` TEXT NOT NULL,
    `HSC_TotalMarks` TEXT NOT NULL,
    `HSC_ObtainedMarks` TEXT NOT NULL,
    `HSC_Percentage` TEXT NOT NULL,
    `HSC_SeatNo` TEXT,
    `11th_Month_Year` TEXT,
    `11th_BoardName` TEXT,
    `11th_TotalMarks` TEXT,
    `11th_ObtainedMarks` TEXT,
    `11th_Percentage` TEXT,
    `UG_5th_7th_Month_Year` TEXT NOT NULL,
    `UG_5th_7th_BoardName` TEXT NOT NULL,
    `UG_5th_7th_TotalMarks` TEXT NOT NULL,
    `UG_5th_7th_ObtainedMarks` TEXT NOT NULL,
    `UG_5th_7th_Percentage` TEXT NOT NULL,
    `UG_5th_7th_SeatNo` TEXT,
    `UG_6th_Month_Year` TEXT,
    `UG_6th_BoardName` TEXT,
    `UG_6th_TotalMarks` TEXT,
    `UG_6th_ObtainedMarks` TEXT,
    `UG_6th_Percentage` TEXT,
    `UG_6th_SeatNo` TEXT,
    `PG_Month_Year` TEXT NOT NULL,
    `PG_BoardName` TEXT NOT NULL,
    `PG_TotalMarks` TEXT NOT NULL,
    `PG_ObtainedMarks` TEXT NOT NULL,
    `PG_Percentage` TEXT NOT NULL,
    `PG_SeatNo` TEXT,
    `CMAT_Month_Year` TEXT,
    `CMAT_ObtainedMarks` TEXT,
    `CMAT_Percentage` TEXT,
    `CLAT_Month_Year` TEXT,
    `CLAT_ObtainedMarks` TEXT,
    `CLAT_Percentage` TEXT,
    `CAT_Month_Year` TEXT,
    `CAT_ObtainedMarks` TEXT,
    `CAT_Percentage` TEXT,
    `MAT_Month_Year` TEXT,
    `MAT_ObtainedMarks` TEXT,
    `MAT_Percentage` TEXT,
    `ATMA_Month_Year` TEXT,
    `ATMA_ObtainedMarks` TEXT,
    `ATMA_Percentage` TEXT,
    `GUJCET_Given` TEXT,
    `GUJCET_Month_Year` TEXT,
    `GUJCET_ObtainedMarks` TEXT,
    `GUJCET_Percentage` TEXT,
    `JEE_Given` TEXT,
    `JEE_Month_Year` TEXT,
    `JEE_ObtainedMarks` TEXT,
    `JEE_Percentage` TEXT,
    `UGCNET_Month_Year` TEXT,
    `UGCNET_ObtainedMarks` TEXT,
    `UGCNET_Percentage` TEXT,
    `SLET_Month_Year` TEXT,
    `SLET_ObtainedMarks` TEXT,
    `SLET_Percentage` TEXT,
    `UGCJRF_Month_Year` TEXT,
    `UGCJRF_ObtainedMarks` TEXT,
    `UGCJRF_Percentage` TEXT,
    `MPhil_Month_Year` TEXT,
    `MPhil_ObtainedMarks` TEXT,
    `MPhil_Percentage` TEXT,
    `Others_Month_Year` TEXT,
    `Others_ObtainedMarks` TEXT,
    `Others_Percentage` TEXT,
    `Activities` TEXT,
    `NRI` TEXT NOT NULL,
    `HSCStream` TEXT NOT NULL,
    `English` TEXT NOT NULL,
    `ApplicationDate` TEXT NOT NULL,
    `StudentPhoto` TEXT NOT NULL,
    `SSCMarksheet` TEXT NOT NULL,
    `HSCMarksheet` TEXT NOT NULL,
    `11thMarksheet` TEXT NOT NULL,
    `UG_5th_7th_Marksheet` TEXT NOT NULL,
    `UG_6th_Marksheet` TEXT NOT NULL,
    `PGMarksheet` TEXT NOT NULL,
    `EntranceExamScorecard` TEXT,
    `SchoolLeavingCertificate` TEXT,
    `FirstAttemptCertificate` TEXT,
    `CasteCertificate` TEXT,
    `NonCreamyLayerCertificateissuedafter1_4_2018forSEBC` TEXT,
    `PhotoIDProof` TEXT NOT NULL,
    `ResidentialProof` TEXT NOT NULL,
    `PHCertificate` TEXT,
    `EBCCertificate` TEXT,
    `Payment` TEXT NOT NULL,
    `OfflineCode` TEXT,
    `AreaofResearch` TEXT NOT NULL,
    `GradeConversionPhoto` TEXT,

    `isEligible` BOOLEAN NOT NULL,

    `isActive` BOOLEAN NOT NULL DEFAULT TRUE,
    `isCancelled` BOOLEAN NOT NULL DEFAULT FALSE,
    `isTransferred` BOOLEAN NOT NULL DEFAULT FALSE,
    `cancelledOn` DATE,
    `transferredOn` DATE,
    `cancelledOrTransferredRemarks` TEXT,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `phdReRegistrations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `reason` TEXT,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `admissionExams` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `batchId` INT NOT NULL,
    FOREIGN KEY (`batchId`) REFERENCES `phdBatches`(`Id`),

    `dateAndTimeOfWrittenExam` DATETIME,
    `dateAndTimeOfPresentation` DATETIME,

    `maximumMarksOfWrittenExam` DECIMAL(5, 2),
    `maximumMarksOfPresentation` DECIMAL(5, 2),

    `writtenExamPaperFileName` VARCHAR(255),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `admissionExamResults` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `examId` INT NOT NULL,
    FOREIGN KEY (`examId`) REFERENCES `admissionExams`(`id`),

    `obtainedMarksInWrittenExam` DECIMAL(5, 2),
    `obtainedMarksInPresentation` DECIMAL(5, 2),

    `hasPassed` BOOLEAN NOT NULL DEFAULT FALSE,

    `proposalPresentationFileName` VARCHAR(255),
    `presentation_ppt_file` VARCHAR(255),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `feePayments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `feeType` ENUM('Regular', 'Re-registration') NOT NULL,
    `paidOnDate` DATE NOT NULL,
    `remarks` TEXT,
    `receiptFileName` VARCHAR(255),

    `paymentMode` ENUM('Online', 'Bank') NOT NULL,
    
    `chequeNumber` VARCHAR(255),
    `bankName` VARCHAR(255),
    `ifsc` VARCHAR(255),
    `branchName` VARCHAR(255),
    
    `onlineTransactionReferenceNumber` VARCHAR(255),
    `onlinePaymentProofFileName` VARCHAR(255),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- ====================
-- Guides & allocation
-- ====================

CREATE TABLE `phdGuides` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `name` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(255) NOT NULL,
    `isActive` BOOLEAN NOT NULL DEFAULT TRUE,

    `maximumNumberOfScholars` INT NOT NULL DEFAULT 0,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);

CREATE TABLE `guideAllocationPresentations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `batchId` INT NOT NULL,
    FOREIGN KEY (`batchId`) REFERENCES `phdBatches`(`id`),

    `name` VARCHAR(255) NOT NULL,
    `scheduledOn` DATE NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);

CREATE TABLE `guideAllocationPresentationAttendance` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `batchId` INT NOT NULL,
    FOREIGN KEY (`batchId`) REFERENCES `phdBatches`(`id`),

    `guideAllocationPresentationId` INT NOT NULL,
    FOREIGN KEY (`guideAllocationPresentationId`) REFERENCES `guideAllocationPresentations`(`id`),

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `hasAttended` BOOLEAN NOT NULL DEFAULT TRUE,

    `topicName` TEXT,
    `presentationPdfFileName` VARCHAR(255),
    `remarks` TEXT,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);

CREATE TABLE `guidePreferences` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `batchId` INT NOT NULL,
    FOREIGN KEY (`batchId`) REFERENCES `phdBatches`(`id`),

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `guideId` INT NOT NULL,
    FOREIGN KEY (`guideId`) REFERENCES `phdGuides`(`id`),

    `guideAllocationPresentationId` INT NOT NULL,
    FOREIGN KEY (`guideAllocationPresentationId`) REFERENCES `guideAllocationPresentations`(`id`),

    `guideAllocationPresentationAttendanceId` INT NOT NULL,
    FOREIGN KEY (`guideAllocationPresentationAttendanceId`) REFERENCES `guideAllocationPresentationAttendance`(`id`),

    `priority` INT NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);

CREATE TABLE `guideAllocations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `guideId` INT NOT NULL,
    FOREIGN KEY (`guideId`) REFERENCES `phdGuides`(`id`),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);


-- ====================
-- RDC
-- ====================

CREATE TABLE `externalPanelMembers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `fullName` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(255) NOT NULL,
    `organization` VARCHAR(255) NOT NULL,
    `mobileNumber` VARCHAR(255) NOT NULL,
    `emailAdderess` VARCHAR(255) NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `scheduledRdcs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `name` VARCHAR(255) NOT NULL,
    `scheduledOn` DATE NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `scheduledRdcTimeSlots` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `scheduledRdcId` INT NOT NULL,
    FOREIGN KEY (`scheduledRdcId`) REFERENCES `scheduledRdcs`(`id`),

    `name` VARCHAR(255) NOT NULL,
    `fromTime` TIME NOT NULL,
    `toTime` TIME NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `staffRdcTimeSlotAllocation` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `staffId` INT NOT NULL,
    FOREIGN KEY (`staffId`) REFERENCES `phdStaff`(`Id`),

    `scheduledRdcTimeSlotId` INT NOT NULL,
    FOREIGN KEY (`scheduledRdcTimeSlotId`) REFERENCES `scheduledRdcTimeSlots`(`id`),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `externalRdcTimeSlotAllocation` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `externalPanelMemberId` INT NOT NULL,
    FOREIGN KEY (`externalPanelMemberId`) REFERENCES `externalPanelMembers`(`id`),

    `scheduledRdcTimeSlotId` INT NOT NULL,
    FOREIGN KEY (`scheduledRdcTimeSlotId`) REFERENCES `scheduledRdcTimeSlots`(`id`),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `studentRdcTimeSlotAllocation` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`Id`),

    `scheduledRdcTimeSlotId` INT NOT NULL,
    FOREIGN KEY (`scheduledRdcTimeSlotId`) REFERENCES `scheduledRdcTimeSlots`(`id`),

    `turnNumber` INT NOT NULL,
    `hasAttended` BOOLEAN,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `rdcFeedback` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `studentId` INT NOT NULL,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `scheduledRdcId` INT NOT NULL,
    FOREIGN KEY (`scheduledRdcId`) REFERENCES `scheduledRdcs`(`id`),

    `rdcTimeSlotAllocationId` INT NOT NULL,
    FOREIGN KEY (`rdcTimeSlotAllocationId`) REFERENCES `rdcTimeSlotAllocation`(`id`),

    `feedbackText` TEXT NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `externalPanelMembers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `fullName` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(255) NOT NULL,
    `organization` VARCHAR(255) NOT NULL,
    `mobileNumber` VARCHAR(255) NOT NULL,
    `emailAdderess` VARCHAR(255) NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `rdcExternalPanelMembers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `rdcTimeSlotAllocationId` INT NOT NULL,
    FOREIGN KEY (`rdcTimeSlotAllocationId`) REFERENCES `rdcTimeSlotAllocation`(`id`),

    `externalPanelMemberId` INT NOT NULL,
    FOREIGN KEY (`externalPanelMemberId`) REFERENCES `externalPanelMembers`(`id`),

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- ====================
-- Emails & templates
-- ====================

CREATE TABLE `emailTemplateTypes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `name` VARCHAR(255) NOT NULL,
    
    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `emailTemplates` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `emailTemplateTypeId` INT NOT NULL,
    FOREIGN KEY (`emailTemplateTypeId`) REFERENCES `emailTemplateTypes`(`id`),

    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    
    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `sentEmails` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `emailTemplateTypeId` INT NOT NULL,
    FOREIGN KEY (`emailTemplateTypeId`) REFERENCES `emailTemplateTypes`(`id`),

    `emailTemplateId` INT NOT NULL,
    FOREIGN KEY (`emailTemplateId`) REFERENCES `emailTemplates`(`id`),

    `actualSubject` VARCHAR(255) NOT NULL,
    `actualBody` TEXT NOT NULL,

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `sentEmailRecipients` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,

    `sentEmailId` INT NOT NULL,
    FOREIGN KEY (`sentEmailId`) REFERENCES `sentEmails`(`id`),

    -- at least one must be present

    `studentId` INT,
    FOREIGN KEY (`studentId`) REFERENCES `phdStudents`(`id`),

    `guideId` INT,
    FOREIGN KEY (`guideId`) REFERENCES `phdGuides`(`id`),

    `externalPanelMemberId` INT,
    FOREIGN KEY (`externalPanelMemberId`) REFERENCES `externalPanelMembers`(`id`),

    `otherRecipientEmailAddresses` TEXT, -- comma-separated
    `ccEmailAddresses` TEXT, -- comma-separated
    `bccEmailAddresses` TEXT, -- comma-separated

    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- This is just a dummy table for reference. To be removed later.
CREATE TABLE `staff` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,

  `staffType` ENUM('Teaching', 'Non-Teaching - Admin', 'Non-Teaching - Lab') NOT NULL DEFAULT 'Teaching',

  `orderIndex` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `emailAddress` VARCHAR(255) NOT NULL,
  `designation` VARCHAR(255) NOT NULL,
  `qualification` VARCHAR(255),
  `experience` VARCHAR(255),
  `specialization` VARCHAR(255),
  `researchArea` VARCHAR(255),
  `briefDescription` TEXT,
  `imageFileName` VARCHAR(255),
  `websiteLink` VARCHAR(255),
  `googleScholarLink` VARCHAR(255),
  `linkedinLink` VARCHAR(255),
  `researchgateLink` VARCHAR(255),
  `orcidLink` VARCHAR(255),
  `scopusLink` VARCHAR(255),
  `isActive` BOOLEAN NOT NULL DEFAULT TRUE,
  `isAdmin` BOOLEAN NOT NULL DEFAULT FALSE,

  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
