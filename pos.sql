-- ===============DenominationMaster Table====================

CREATE TABLE [dbo].[DenominationMaster](
	[DenominationID] [int] IDENTITY(1,1) NOT NULL,
	[DepotCode] [varchar](20) NULL,
	[TerminalID] [varchar](50) NULL,
	[DenominationType] [varchar](20) NULL,
	[DenominationDate] [date] NULL,
	[TotalAmount] [decimal](10, 2) NULL,
	[CreatedBy] [varchar](20) NULL,
	[CreatedAt] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[DenominationID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]


-- ===============DenominationDetails Table====================

CREATE TABLE [dbo].[DenominationDetails](
	[DenominationID] [int] NULL,
	[NoteID] [int] NULL,
	[TerminalID] [varchar](50) NULL,
	[NoteQuantity] [int] NULL
) ON [PRIMARY]


-- ===============SP_InvoiceDataWithSumsForCashClose Procedure====================

ALTER PROCEDURE [dbo].[SP_InvoiceDataWithSumsForCashClose]
    @DepotCode NVARCHAR(10),
    @TerminalID NVARCHAR(50),
    @DenominationDate DATE,
    @CreatedBy NVARCHAR(50)
AS

/*
Declare @DepotCode NVARCHAR(10),
		@TerminalID NVARCHAR(50),
		@DenominationDate DATE,
		@CreatedBy NVARCHAR(50)

SET @DepotCode = 'S030'
SET @TerminalID = 'MIS-hemel'
SET @DenominationDate = '2024-04-29'
SET @CreatedBy = 'supadmin'
--EXEC SP_InvoiceDataWithSumsForCashClose 'S030', 'MIS-hemel', '2024-04-29', 'supadmin'
--*/
SET NOCOUNT ON;

DECLARE @PrepareDate DATETIME;

SELECT TOP 1 @PrepareDate = CreatedAt FROM DenominationMaster 
WHERE DepotCode = @DepotCode AND TerminalID = @TerminalID  
						 AND DenominationDate = @DenominationDate
						 AND CreatedBy = @CreatedBy
						 AND DenominationType = 'Opening'
ORDER BY CreatedAt DESC

DECLARE @NSISum DECIMAL(18, 2);

SELECT *
INTO #TempInvoice
FROM Invoice
WHERE PrepareDate BETWEEN @PrepareDate AND GETDATE()

SELECT @NSISum = SUM(NSI) FROM #TempInvoice


--DECLARE @PaidSum DECIMAL(18, 2)
--DECLARE @ReturnSum DECIMAL(18, 2)
--DECLARE @CashSum DECIMAL(18, 2)

--SELECT @PaidSum = SUM(IT.TenderAmount)
--FROM InvoiceTender IT
--INNER JOIN Invoice I ON IT.InvoiceNo = I.InvoiceNo
--WHERE I.PrepareDate BETWEEN @PrepareDate AND GETDATE() AND IT.TenderID = 'PAID'

--SELECT @ReturnSum = SUM(IT.TenderAmount)
--FROM InvoiceTender IT
--INNER JOIN Invoice I ON IT.InvoiceNo = I.InvoiceNo
--WHERE I.PrepareDate BETWEEN @PrepareDate AND GETDATE() AND IT.TenderID = 'RETN'

--Select @CashSum = @PaidSum - @ReturnSum

SELECT SUM(CASE WHEN IT.TenderID = 'PAID' THEN IT.TenderAmount ELSE 0 END) AS PaidSUm,
        SUM(CASE WHEN IT.TenderID = 'RETN' THEN IT.TenderAmount ELSE 0 END) AS RetrunSum,
	   SUM(CASE WHEN IT.TenderID = 'PAID' THEN IT.TenderAmount ELSE 0 END) - SUM(CASE WHEN IT.TenderID = 'RETN' THEN IT.TenderAmount ELSE 0 END) AS CashSum
FROM InvoiceTender IT
INNER JOIN Invoice I ON IT.InvoiceNo = I.InvoiceNo
WHERE I.PrepareDate BETWEEN @PrepareDate AND GETDATE();

SELECT * FROM #TempInvoice
drop table #TempInvoice

SET NOCOUNT OFF


-- ===============Notes Table====================

CREATE TABLE [dbo].[Notes](
	[NoteID] [int] IDENTITY(1,1) NOT NULL,
	[NoteAmount] [int] NULL,
	[Status] [varchar](1) NULL,
PRIMARY KEY CLUSTERED 
(
	[NoteID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]


-- ===============Terminal Table====================

CREATE TABLE [dbo].[Terminal](
	[TerminalID] [varchar](20) NOT NULL,
	[Terminal] [varchar](50) NOT NULL,
	[AuthorisationType] [varchar](10) NOT NULL,
	[IPAddress] [varchar](50) NULL,
	[PrinterName] [varchar](100) NULL,
	[AdministrativeUserID] [varchar](100) NULL,
	[AdministrativeUserPassword] [varchar](100) NULL,
	[Active] [varchar](1) NOT NULL,
	[TerminalSAPID] [varchar](20) NULL,
 CONSTRAINT [PK_Terminal] PRIMARY KEY CLUSTERED 
(
	[TerminalID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]


-- ===============Search Product Procedure====================
ALTER PROCEDURE [dbo].[SP_ScanProductList](
	@Search VARCHAR(500)
)

AS 
SET NOCOUNT ON

/*
DECLARE @Search VARCHAR(500)

SET @Search = '1011001'

-- EXEC SP_ScanProductList ''
--*/


DECLARE @vSearch VARCHAR(500);
DECLARE @vProductName VARCHAR(300);
DECLARE @vBarcode VARCHAR(300);
DECLARE @vPrice NUMERIC(18,2);
DECLARE @vQTY NUMERIC(18,2);
DECLARE @vWeighingProduct VARCHAR(1)

SET @vSearch = @Search;

SET @vBarcode= CASE WHEN LEN(@Search)>12 AND (LEFT(@Search,2)='20' OR LEFT(@Search,2)='24') 
						THEN RIGHT(LEFT(@Search,7),5)
				 WHEN CHARINDEX('|',@Search)=0 AND ISNUMERIC(@Search)=1 
					THEN @Search
				ELSE '' END

SET @vProductName= CASE WHEN CHARINDEX('|',@Search)=0 AND ISNUMERIC(@Search)=0 
								THEN  @Search 
						WHEN CHARINDEX('|',@Search)>1 AND ISNUMERIC(@Search)=0 
								THEN  LEFT(@Search, CHARINDEX('|',@Search)-1 ) 
					ELSE '' END

SET @vPrice= CASE WHEN CHARINDEX('|',@Search)>0 
								THEN  CASE WHEN ISNUMERIC(RIGHT(@Search, LEN(@Search)-CHARINDEX('|',@Search) ))=0 
										THEN '0' 
								ELSE RIGHT(@Search, LEN(@Search)-CHARINDEX('|',@Search) ) 
										END
				ELSE '0' END


-- SINCE in Weighing Code contains weight in (8th to 12th position)
SET @vQTY= CASE WHEN LEN(@Search)>12 AND (LEFT(@Search,2)='20' OR LEFT(@Search,2)='24') 
						THEN CONVERT(NUMERIC(18,3),RIGHT(LEFT(@Search,9),2)+'.'+ RIGHT(LEFT(@Search,12),3))
			ELSE
				1
			END 

SET @vWeighingProduct=CASE WHEN LEN(@Search)>12 AND (LEFT(@Search,2)='20' OR LEFT(@Search,2)='24')  
				AND ISNUMERIC(CONVERT(NUMERIC(18,3),RIGHT(LEFT(@Search,9),2)+'.'+ RIGHT(LEFT(@Search,12),3)))=1 
						THEN 'Y'
				ELSE	
					'N'
				END


CREATE TABLE #prod(
	ProductCode VARCHAR(50) Not Null Default '',
	BarCode VARCHAR(50) Not Null Default '',
	DisableStatus VARCHAR(1) Not Null Default '0',
	ProductWeight VARCHAR(300) Not Null Default '0'
)


IF @vBarcode <> ''
	BEGIN
		INSERT INTO #prod 
		SELECT ProductCode,BarCode,0,0 from ProductBarcode  
		Where BarCode = @vBarcode	
	END
ELSE IF @vProductName <> ''
	BEGIN
		INSERT INTO #prod 
		SELECT P.ProductCode,B.BarCode,0,0 from Product P
		INNER JOIN ProductBarcode B ON P.ProductCode = B.ProductCode 
		 WHERE CHARINDEX(@vProductName,P.ProductName) > 0  AND P.Active = 'Y' 
	END 
ELSE
	BEGIN
		INSERT INTO #prod
		SELECT P.ProductCode,B.BarCode,0,0 from Product P
		INNER JOIN ProductBarcode B ON P.ProductCode = B.ProductCode
		WHERE P.Active = 'Y'
	END

UPDATE #prod 
	SET DisableStatus = 1 
	WHERE @vWeighingProduct='Y'

SELECT DISTINCT ProductCode,ProductName,StockQuantity, Quantity, UnitPrice,SalesTP,Flag, VATPerc,
'' BarCode,
DisableStatus,PackSize, AllowNegative, InvoiceType, VATDiscPerc
FROM (
SELECT PT.ProductCode,PT.ProductName,SB.BatchQTY AS StockQuantity, @vQTY as Quantity, PP.UnitPrice,PP.UnitPrice AS SalesTP,'Stock' Flag, PP.VATPerc,
P.BarCode,P.DisableStatus,PT.PackSize, PT.AllowNegative,  'I' as InvoiceType, PP.VATDiscPerc
FROM #prod P
INNER JOIN Product PT ON P.ProductCode= PT.ProductCode 
INNER JOIN StockBatch SB ON SB.ProductCode  = PT.ProductCode AND SB.Locked = 'N'
INNER JOIN ProductPrice PP ON PT.ProductCode = PP.ProductCode 
WHERE (PP.UnitPrice = CASE WHEN @vPrice = 0 THEN PP.UnitPrice ELSE  @vPrice  END)  
AND (PT.ProductCode=CASE WHEN @vPrice = 0 THEN PT.ProductCode ELSE  P.BarCode  END)
--AND SB.BatchQTY > 0
UNION
SELECT PT.ProductCode,PT.ProductName,C.StockQty AS StockQuantity, @vQTY as Quantity,PP.UnitPrice,C.UnitPrice AS SalesTP,'Clearance' Flag,0 VATPerc,
'' BarCode,P.DisableStatus,PT.PackSize, PT.AllowNegative, 'I' as InvoiceType,PP.VATDiscPerc
FROM #prod P
INNER JOIN Product PT ON P.ProductCode= PT.ProductCode 
INNER JOIN ProductPrice PP ON PP.ProductCode = Pt.ProductCode
INNER JOIN StockClearance C ON C.ProductCode =  PT.ProductCode AND C.Locked = 'N' AND C.StockQty > 0
WHERE (C.UnitPrice = CASE WHEN @vPrice = 0 THEN C.UnitPrice ELSE  @vPrice  END)  --AND SB.BatchQTY > 0
AND (PT.ProductCode=CASE WHEN @vPrice = 0 THEN PT.ProductCode ELSE  P.BarCode  END)
) SQ
ORDER BY 1,3

DROP TABLE #prod

SET NOCOUNT OFF



-- ===============Recall Procedure====================

ALTER PROCEDURE [dbo].[SP_RecallTempInvoice]
    @vInvoiceNo VARCHAR(20),
	@vHoldNo INT,
	@vDepotCode VARCHAR(10)
AS
SET NOCOUNT ON 
/*
DECLARE @vInvoiceNo VARCHAR(20);
DECLARE @vHoldNo INT;
DECLARE @vDepotCode VARCHAR(10)


SET @vInvoiceNo = '29846'
SET @vHoldNo = 1
SET @vDepotCode = 'S030'
--EXEC [SP_RecallTempInvoice] '29846',1,'S030'
--*/


 SELECT * FROM TempInvoice WHERE InvoiceNo = @vInvoiceNo AND HoldNo = @vHoldNo
 
 SELECT P.ProductName,P.PackSize, TI.ProductCode, TI.UnitPrice, CONVERT(NUMERIC(16,2),TI.SalesQTY) as Quantity,SalesTP,CONVERT(NUMERIC(16,2),0) AS StockQuantity,TI.VAT,TP,NSI,PP.VATPerc,
	CASE WHEN IsWeighingProduct = 'Y' THEN 1 ELSE 0 END AS DisableStatus,TI.TransType as InvoiceType, PP.VATDiscPerc  -- vat discount need to update
	into #tempDetails
  FROM TempInvoiceDetails TI
 INNER JOIN Product P ON P.ProductCode = TI.ProductCode
 INNER JOIN ProductPrice PP ON PP.ProductCode = P.ProductCode AND PP.DepotCode = @vDepotCode 
 WHERE TI.InvoiceNo = @vInvoiceNo AND TI.HoldNo = @vHoldNo

 UPDATE #tempDetails
	SET StockQuantity = S.BatchQTY
FROM #tempDetails D
INNER JOIN StockBatch S ON D.ProductCode = S.ProductCode AND S.DepotCode = @vDepotCode

UPDATE #tempDetails
	SET StockQuantity  = S.StockQty
FROM #tempDetails D 
INNER JOIN StockClearance S ON  S.ProductCode = D.ProductCode AND D.SalesTP = S.UnitPrice AND S.DepotCode = @vDepotCode  
WHERE D.SalesTP <> D.UnitPrice 

select * from #tempDetails

 Delete from TempInvoiceDetails  WHERE InvoiceNo = @vInvoiceNo AND HoldNo = 0

 Delete from TempInvoice  WHERE InvoiceNo = @vInvoiceNo AND HoldNo = 0

INSERT INTO TempInvoice
SELECT InvoiceNo,0 HoldNo,CustomerCode,TP,VAT,Discount,ManualDiscount,InvoiceDiscount,LoyaltyDiscount,
	OtherDiscountType,OtherDiscount,VATDiscount,NET,NSI,InvoicePrint,InvoicePrintTime,SDVAT,SDVATDiscount
 FROM TempInvoice WHERE InvoiceNo = @vInvoiceNo AND HoldNo = @vHoldNo

INSERT INTO TempInvoiceDetails
SELECT Invoiceno,0 HoldNo,ProductCode,TransType,UnitPrice,UnitVAT,SalesTP,SalesVat,SalesQTYOld,SalesQTY,
BonusQTY,ManBonusQTY,TP,VAT,DiscountID,Discount,ManualDiscount,InvoiceDiscount,LoyaltyDiscount,OtherDiscount,
	VATDiscount,NET,NSI,BonusOff,DiscGiven,DiscountReturn,BonusFor,Clearance,VATDiscPerc,MobileNo,LineNumber,CardDiscount,SDVATRate,SDVAT,SDVATDiscount,VATRate,IsWeighingProduct
 FROM TempInvoiceDetails WHERE InvoiceNo = @vInvoiceNo AND HoldNo = @vHoldNo

Delete from TempInvoiceDetails  WHERE InvoiceNo = @vInvoiceNo AND (HoldNo = @vHoldNo OR HoldNo = 0)

Delete from TempInvoice  WHERE InvoiceNo = @vInvoiceNo AND (HoldNo = @vHoldNo OR HoldNo = 0)

drop table #tempDetails

SET NOCOUNT OFF


-- ============================Customer Tender Procedure==============================
USE [EPS]
GO
/****** Object:  StoredProcedure [dbo].[SP_CustomerTender]    Script Date: 1/8/2024 11:16:12 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER PROCEDURE [dbo].[SP_CustomerTender]
   -- @vCustomerCode VARCHAR(30)
AS
SET NOCOUNT ON 

/*DECLARE  @vCustomerCode VARCHAR(30)
SET @vCustomerCode = ''
--EXEC [SP_CustomerTender] 'asd'
SP_CustomerTender ''

--*/

--SELECT TenderID,TenderType,RowNo,Locked,RefColumn,DepositType  INTO #temp FROM TenderType WHERE Active = 'Y'

--IF @vCustomerCode <> ''
--	BEGIN
--		SELECT C.CustomerName, ISNULL(CP.LoyaltyPoints,0) AS LoyaltyPoints , ISNULL(CP.LoyaltyAmount,0 ) AS LoyaltyAmount, C.DepotCode,MaturePoints INTO #cus FROM Customer C
--		LEFT JOIN CustomerPoints CP ON CP.CustomerCode = C.CustomerCode
--		INNER JOIN LoyaltySettings LS ON LS.LoyaltySettingsId = C.LoyaltySettingsId
--		WHERE C.CustomerCode = @vCustomerCode  

--		IF (SELECT LoyaltyPoints FROM #cus) < (SELECT MaturePoints FROM #cus) OR (SELECT COUNT(*) FROM #cus) = 0 
--			BEGIN 
--				Update #temp SET Locked = 'Y' WHERE TenderID = 'LCUS'
--			END
		
--		SELECT * FROM #cus 			
--		DROP TABLE #cus
--	END 
--ELSE
--	BEGIN
--		Update #temp SET Locked = 'Y' WHERE TenderID = 'LCUS'
--	END

--SELECT *,'' AS TenderValue FROM #temp ORDER BY RowNo

--DROP TABLE #temp

SELECT TenderID,TenderType,RowNo,Locked,RefColumn,DepositType, '' AS TenderValue, '' AS TenderRefValue  FROM TenderType WHERE Active = 'Y' ORDER BY RowNo


SET NOCOUNT OFF



-- ==============================Insert Invoice Procedure=============================

ALTER PROCEDURE [dbo].[SP_InsertInvoiceInfo](
	@InvoiceNo AS VARCHAR(30),
	@Customer AS VARCHAR(20),
	@CashTaken AS NUMERIC(18,2),
	@ReceiveCash AS NUMERIC(18,2),
	@ReceiveCard AS NUMERIC(18,2),
	@ReceiveCoupon AS NUMERIC(18,2),
	@ReceiveGiftVoucher AS NUMERIC(18,2),
	@TerminalID AS VARCHAR(50),
	@PreparedBy AS VARCHAR(50)
)

AS

/*
DECLARE @InvoiceNo AS VARCHAR(30)
DECLARE @Customer AS VARCHAR(10),
		@CashTaken AS NUMERIC(18,2),
		@ReceiveCash AS NUMERIC(18,2),
		@ReceiveCard AS NUMERIC(18,2),
		@ReceiveCoupon AS NUMERIC(18,2),
		@ReceiveGiftVoucher AS NUMERIC(18,2),
		@TerminalID AS VARCHAR(50),
		@PreparedBy AS VARCHAR(50)

SET @InvoiceNo = '29846' 
SET @Customer = ''
SET @ReceiveCash =0
SET @ReceiveCard =0
SET @ReceiveCoupon=0
SET @CashTaken =0
SET @ReceiveGiftVoucher=0
SET @PreparedBy='29846'
SET @TerminalID = 'D062BOSN'
--[SP_InsertInvoiceInfo] '29846' ,'', '0', '0', '0', '0', '0', 'D062BOSN', '29846'
--*/
SET NOCOUNT ON
SET QUOTED_IDENTIFIER OFF

DECLARE @vInvoiceNo AS VARCHAR(20),
		@vDepotCode AS VARCHAR(20),	
		@ReturnDiscount AS NUMERIC(18,2),
		@ReturnAmount AS NUMERIC(18,2),
		@ReturnTotal AS NUMERIC(18,2),
		@vLoyaltyId AS VARCHAR(25)

DECLARE @InvoiceDate AS DATETIME

SET @InvoiceDate= LEFT(GETDATE(),11)
SELECT @vDepotCode= DepotCode
FROM Depot
WHERE ActiveDepot='Y'

SELECT @vInvoiceNo=@vDepotCode+ RIGHT(CONVERT(varchar(8),@InvoiceDate,112),6) +FORMAT(CONVERT(INT,RIGHT(ISNULL(MAX(InvoiceNo),0),4))+1,'0000') 
FROM Invoice
WHERE InvoiceDate=@InvoiceDate
AND @vDepotCode= DepotCode

SELECT @ReturnDiscount=ABS(SUM(ISNULL(Discount,0))) ,
@ReturnDiscount=ABS(ISNULL(SUM(NET),0)) ,
@ReturnTotal= ISNULL(SUM(Net-Discount),0) 
FROM TempInvoiceDetails D
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0
AND TransType='R'

SELECT 
InvoiceNo,CONVERT(varchar(6),@InvoiceDate,112) AS  InvoicePeriod,@InvoiceDate AS InvoiceDate,@vDepotCode AS DepotCode,
	'N' SalesType,ISNULL(CustomerCode,'') AS CustomerCode,TP,VAT,Discount,ManualDiscount,
	InvoiceDiscount,LoyaltyDiscount,OtherDiscountType,OtherDiscount,VATDiscount,NET,NSI,
	ROUND(NET,0)-Net AS Roundoff,
	ROUND(NET,0) AS  ReceiveAmount,
	@ReceiveCash AS ReceiveCash,
	@ReceiveCard  AS ReceiveCard,
	@ReceiveCoupon AS ReceiveCoupon,
	@ReceiveGiftVoucher AS ReceiveGiftVouc,
	@ReturnDiscount CreditNoteDiscount,
	ISNULL(@ReturnAmount,0) CreditNoteAmount,
	@ReturnTotal CreditNoteTotal,
	'Y' AS Paid,
	@CashTaken AS CashTaken,
	@CashTaken-ROUND(NET,0) ChangeAmount,
	'' ReturnInvoiceNo,
	@TerminalID AS TerminalID,
	@PreparedBy AS PrepareBy,
	GETDATE() AS PrepareDate,
	'' EditBy,
	NULL AS EditDate,
	1 AS PrintCount,
	'' Remarks,
	SDVAT,SDVATDiscount

INTO #Inv
FROM tempInvoice
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0

SELECT Invoiceno,ProductCode,TransType,UnitPrice,UnitVAT,SalesTP,SalesVat,--SalesQTYOld,
	SalesQTY,BonusQTY,ManBonusQTY,TP,VAT,DiscountID,Discount,ManualDiscount,InvoiceDiscount,LoyaltyDiscount,
	OtherDiscount,VATDiscount,NET,NSI,'' AS Remarks,--BonusOff,DiscGiven,DiscountReturn,BonusFor,Clearance,VATDiscPerc,MobileNo,LineNumber,CardDiscount,
	SDVATRate,SDVAT,SDVATDiscount,VATRate, 0 as syncStatus
InTO #InvDet
FROM TempInvoiceDetails D
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0

SELECT InvoiceNo, TenderID, TenderAmount, RefNo, DepositNo,syncStatus
INTO #InvTender
FROM TempInvoiceTender D
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0

UPDATE #Inv
SET InvoiceNo=@vInvoiceNo

UPDATE #InvDet
SET InvoiceNo=@vInvoiceNo

UPDATE #InvTender
SET InvoiceNo=@vInvoiceNo

INSERT INTO Invoice
SELECT * FROM #Inv

INSERT INTO InvoiceDetails
SELECT * FROM #InvDet

INSERT INTO InvoiceTender
SELECT * FROM #InvTender

-- update stock Batch 
UPDATE StockBatch
	SET BatchQTY = BatchQTY - (D.SalesQty+D.BonusQTY)
FROM TempInvoiceDetails D
INNER JOIN StockBatch S ON D.ProductCode = S.ProductCode AND S.DepotCode = @vDepotCode
WHERE D.TransType = 'I' AND D.SalesTP = D.UnitPrice AND InvoiceNo = @InvoiceNo AND HoldNo = 0

UPDATE StockBatch
	SET BatchQTY = BatchQTY - D.BonusQTY
FROM TempInvoiceDetails D
INNER JOIN StockBatch S ON D.ProductCode = S.ProductCode AND S.DepotCode = @vDepotCode
WHERE D.TransType = 'I' AND D.SalesTP <> D.UnitPrice AND InvoiceNo = @InvoiceNo AND HoldNo = 0
 
-- Handle RETURN (TransType = 'R') — increase stock
UPDATE S
SET S.BatchQTY = S.BatchQTY + (D.SalesQty + D.BonusQTY)
FROM TempInvoiceDetails D
INNER JOIN StockBatch S ON D.ProductCode = S.ProductCode AND S.DepotCode = @vDepotCode
WHERE D.TransType = 'R' AND D.SalesTP = D.UnitPrice AND D.InvoiceNo = @InvoiceNo AND D.HoldNo = 0

-- Handle BONUS for RETURN
UPDATE S
SET S.BatchQTY = S.BatchQTY + D.BonusQTY
FROM TempInvoiceDetails D
INNER JOIN StockBatch S ON D.ProductCode = S.ProductCode AND S.DepotCode = @vDepotCode
WHERE D.TransType = 'R' AND D.SalesTP <> D.UnitPrice AND D.InvoiceNo = @InvoiceNo AND D.HoldNo = 0

-- update stock clearance
UPDATE StockClearance
	SET StockQty  = StockQty - D.SalesQty
FROM TempInvoiceDetails D 
INNER JOIN StockClearance S ON  S.ProductCode = D.ProductCode AND D.SalesTP = S.UnitPrice AND S.DepotCode = @vDepotCode  
WHERE D.SalesTP <> D.UnitPrice AND InvoiceNo=@InvoiceNo AND HoldNo=0

DECLARE @CustomerType AS VARCHAR(10),
		@vShoppingRate INT,
		@vPointRate NUMERIC(18,2),
		@vConvertionRate NUMERIC(18,2),
		@vReedemdPoint NUMERIC(18,2),
		@vRedeemedAmount NUMERIC(18,2),
		@vNet NUMERIC(18,2)

SELECT @vNet= NET FROM TempInvoice
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0

SELECT @vShoppingRate= L.PurChaseAmount, @vPointRate=L.Points, @vConvertionRate=L.Amount FROM Customer C
INNER JOIN LoyaltySettings L ON C.LoyaltySettingsId= L.LoyaltySettingsId
WHERE CustomerCode=@Customer
SET @vShoppingRate=ISNULL(@vShoppingRate,0)
SET @vPointRate=ISNULL(@vPointRate,0)
SET @vConvertionRate= ISNULL(@vConvertionRate,0)

SELECT @vReedemdPoint= convert(numeric(18,2),RefNo), @vRedeemedAmount= TenderAmount 
FROM TempInvoiceTender
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0 AND TenderID = 'LCUS' 

SET @vReedemdPoint=ISNULL(@vReedemdPoint,0)
SET @vRedeemedAmount=ISNULL(@vRedeemedAmount,0)
IF @vReedemdPoint>0 
	BEGIN
		SELECT @vLoyaltyId= @vDepotCode+RIGHT(CONVERT(VARCHAR(4),GETDATE(),112),2) +FORMAT(ISNULL(MAX(CONVERT(INT,RIGHT(LoyaltyId,7))),0)+1,'0000000') FROM LoyaltyDetails
		WHERE LEFT(LoyaltyId,6)=@vDepotCode+RIGHT(CONVERT(VARCHAR(4),GETDATE(),112),2) 
		INSERT INTO LoyaltyDetails
		SELECT @vLoyaltyId, @vDepotCode,@Customer, GETDATE(),@InvoiceNo,'IR',(-1)*(@vReedemdPoint),(-1)*(@vRedeemedAmount),'N',1

		IF (SELECT count(*) from CustomerPoints where  CustomerCode=@Customer AND DepotCode=@vDepotCode) > 0 
			BEGIN
				UPDATE CustomerPoints
				SET LoyaltyPoints=LoyaltyPoints-@vReedemdPoint, LoyaltyAmount=LoyaltyAmount-@vRedeemedAmount
				WHERE CustomerCode=@Customer
					AND DepotCode=@vDepotCode
			END
		ELSE
			BEGIN
				INSERT INTO CustomerPoints VALUES (@Customer,@vDepotCode,(-1)*@vReedemdPoint,(-1)*@vRedeemedAmount,0)
			END
		
	END
	
IF @vShoppingRate>0 AND @vPointRate >0 AND @vNet>0
	BEGIN
		SELECT @vLoyaltyId= @vDepotCode+RIGHT(CONVERT(VARCHAR(4),GETDATE(),112),2) +FORMAT(ISNULL(MAX(CONVERT(INT,RIGHT(LoyaltyId,7))),0)+1,'0000000') FROM LoyaltyDetails
		WHERE LEFT(LoyaltyId,6)=@vDepotCode+RIGHT(CONVERT(VARCHAR(4),GETDATE(),112),2) 
		
		INSERT INTO LoyaltyDetails
		SELECT @vLoyaltyId, @vDepotCode,@Customer, GETDATE(),@InvoiceNo,'IE',(@vNet/@vShoppingRate)*@vPointRate,ROUND((@vNet/@vShoppingRate)*@vPointRate*@vConvertionRate,2),'N',1

		IF (SELECT COUNT(*) FROM CustomerPoints where CustomerCode=@Customer AND DepotCode=@vDepotCode ) > 0
			BEGIN
				UPDATE CustomerPoints
				SET LoyaltyPoints=LoyaltyPoints+((@vNet/@vShoppingRate)*@vPointRate), 
						LoyaltyAmount=LoyaltyAmount+(ROUND((@vNet/@vShoppingRate)*@vPointRate*@vConvertionRate,2))
				WHERE CustomerCode=@Customer
					AND DepotCode=@vDepotCode 
			END
		ELSE 
			BEGIN 
				INSERT INTO CustomerPoints VALUES (@Customer,@vDepotCode,((@vNet/@vShoppingRate)*@vPointRate),(ROUND((@vNet/@vShoppingRate)*@vPointRate*@vConvertionRate,2)),0)
			END
	END

DELETE FROM TempInvoiceTender
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0
DELETE FROM TempInvoiceDetails 
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0

DELETE FROM tempInvoice
WHERE InvoiceNo=@InvoiceNo
AND HoldNo=0

SELECT @vInvoiceNo  AS InvoiceNo

DROP TABLE #Inv
DROP TABLE #InvDet
DROP TABLE #InvTender

SET NOCOUNT OFF


-- ======================Customer Search Procedure===============================

ALTER PROCEDURE [dbo].[SP_CustomerInfoForBill] 
	@vCustomerCode VARCHAR(50) 
	AS
/*
DECLARE @vCustomerCode VARCHAR(25) 

--SET @vCustomerCode ='01199878917'
SET @vCustomerCode ='13131400'

--EXEC SP_CustomerInfoForBill '6998815'
--*/

SET NOCOUNT ON
DECLARE @vCustomerType VARCHAR(50), 
		@vLoyaltyPoints NUMERIC(18,2),
		@vLoyaltyAmount NUMERIC(18,2),
		@vMaturePonits NUMERIC(18,2),
		@vDiscountPerc NUMERIC(18,2),
		@vShoppingBase NUMERIC(18,2),
		@vShoppingAmount NUMERIC(18,2),
		@vCustomerName VARCHAR(250),
		@vBaseOutlet VARCHAR(50), 
		@vMobileNo VARCHAR(50),
		@vSendOTP VARCHAR(1),
		@vSecurityPin VARCHAR(50),
		@vActiveStatus VARCHAR(50),
		@vConvertionRate NUMERIC(18,2),
		@vCreatedDate DATETIME

DECLARE @vCustomerTypeOperational VARCHAR(10),
		@vLoyaltySettingsId NUMERIC(18,2)
				   		 
SELECT @vCustomerType =CustomerType, 		
		@vCustomerName= CustomerName ,
		@vBaseOutlet= DepotCode , 
		@vMobileNo =Mobile,
		@vSendOTP = SendOtp,
		@vSecurityPin= LinkDownPin,
		@vActiveStatus= Active,
		@vLoyaltySettingsId=LoyaltySettingsId,
		@vCreatedDate=CreateDate
FROM Customer C
WHERE CustomerCode =@vCustomerCode 

SELECT @vCustomerTypeOperational= CASE WHEN DiscountGroup='I' THEN 'Discount' ELSE 'Loyalty' END, 
	@vDiscountPerc=Discount
 FROM CustomerType
WHERE CustomerType=@vCustomerType

SELECT @vShoppingAmount=PurchaseAmount, 
	@vShoppingBase=Points, 
	@vConvertionRate= Amount,
	@vMaturePonits=MaturePoints
	FROM LoyaltySettings
WHERE LoyaltySettingsId=@vLoyaltySettingsId

SELECT @vLoyaltyPoints=SUM(LoyaltyPoints), 
	@vLoyaltyAmount=SUM(LoyaltyAmount)
	FROM CustomerPoints
WHERE CustomerCode =@vCustomerCode 

SELECT @vCustomerTypeOperational AS CustomerType , @vCustomerCode as CustomerCode,
		ISNULL(@vLoyaltyPoints,0) AS LoyaltyPoints,
		ISNULL(@vLoyaltyAmount,0) AS LoyaltyAmount,
		ISNULL(@vMaturePonits,0) AS MaturePonits,
		ISNULL(@vDiscountPerc,0) AS DiscountPerc,
		CASE WHEN ISNULL(@vDiscountPerc,0)>0 THEN 0 ELSE ISNULL(@vShoppingBase,0) END  AS ShoppingBase,
		CASE WHEN ISNULL(@vDiscountPerc,0)>0 THEN 0 ELSE ISNULL(@vShoppingAmount,0) END AS ShoppingAmount,
		CASE WHEN ISNULL(@vDiscountPerc,0)>0 THEN 0 ELSE ISNULL(@vConvertionRate,0) END AS ConvertionRate,
		@vCustomerName AS CustomerName,
		@vBaseOutlet AS BaseOutlet, 
		@vMobileNo AS MobileNo,
		@vSendOTP AS SendOTP,
		@vSecurityPin AS SecurityPin,
		@vActiveStatus AS ActiveStatus,
		CASE WHEN @vCustomerTypeOperational='Discount' THEN 'Y' 
			ELSE 
				CASE WHEN ISNULL(@vLoyaltyPoints,0)>=ISNULL(@vMaturePonits,0) THEN 'N'
					ELSE
				'Y' END
			END AS TenderDisable

DECLARE @MinInvoiceValue NUMERIC(18,2),
		@NoInvoiceValue NUMERIC(18,2),
		@NoInvoiceLimit NUMERIC(18,2),
		@TotalInvoiceValue NUMERIC(18,2),
		@TotalInvoiceValueLimit NUMERIC(18,2),
		@vExpiryDuration AS INT,
		@vEligible AS INT,
		@DiscountAmount NUMERIC(18,2),
		@DiscountType VARCHAR(10),
		@CumBasketmimit VARCHAR(10)

IF (SELECT COUNT(*) FROM CustomerRegisterCampaign WHERE LoyaltySettingsId= @vLoyaltySettingsId AND CustomerType=@vCustomerType AND ActiveStatus='Y')>0
	BEGIN
--		SELECT 'Offer Avilable'
--		SELECT * 		FROM CustomerRegisterCampaign WHERE LoyaltySettingsId= @vLoyaltySettingsId AND CustomerType=@vCustomerType

		SELECT @MinInvoiceValue=MinInvoiceValue , @NoInvoiceLimit=CASE WHEN NoInvoiceLimit=0 THEN 999 ELSE NoInvoiceLimit END , 
					@TotalInvoiceValueLimit=CASE WHEN BasketLimit =0 THEN 9999999 ELSE BasketLimit  END ,
					@vExpiryDuration= CASE WHEN DaysToExpire =0 THEN 9999999 ELSE DaysToExpire END,@DiscountAmount= DiscountAmount, @DiscountType=DiscountType 

		FROM CustomerRegisterCampaign 
		WHERE LoyaltySettingsId= @vLoyaltySettingsId AND CustomerType=@vCustomerType AND ActiveStatus='Y'

		IF DATEDIFF(DD, @vCreatedDate, GETDATE())<=@vExpiryDuration
			BEGIN
				SET @vEligible=0
				IF @NoInvoiceLimit=99 
					BEGIN
						SET @vEligible=1
					END
				ELSE 
					BEGIN
						SELECT @TotalInvoiceValue = SUM(DISTINCT Net) 
						FROM Invoice 
						WHERE CustomerCode=@vCustomerCode
						SET @vEligible=CASE WHEN @NoInvoiceValue<= @NoInvoiceLimit THEN 1 ELSE 0 END
					END

				IF @TotalInvoiceValueLimit=9999999 
					BEGIN
						SET @vEligible=1
						SET @TotalInvoiceValue=0
						SET @CumBasketmimit='N'
					END
				ELSE 
					BEGIN
						SELECT @TotalInvoiceValue = SUM(NSI) 
						FROM Invoice 
						WHERE CustomerCode=@vCustomerCode
						SET @vEligible=CASE WHEN @TotalInvoiceValue<= @TotalInvoiceValueLimit THEN 1 ELSE 0 END

						SET @TotalInvoiceValue= @TotalInvoiceValue- @TotalInvoiceValueLimit
						SET @CumBasketmimit='Y'
					END
						   					
			SELECT 'Y' AS OfferEligible,	@MinInvoiceValue AS MinBasketLimit, @CumBasketmimit AS BasketCum, @TotalInvoiceValue  AS AvailAbleBasketLimit, @DiscountAmount AS DiscountAmount, @DiscountType AS DiscountType
			END

		ELSE 
			BEGIN
			--SELECT 'Durantion Expired'
			SET @vEligible=0
			END 	
	END
ELSE
	BEGIN
		--SELECT 'Offer Not Avilable'
		SET @vEligible=0
	END
	IF @vEligible=0
	SELECT 'N' AS OfferEligible,	0 AS MinBasketLimit, @CumBasketmimit AS BasketCum, 0 AS AvailAbleBasketLimit, 0 AS DiscountAmount, '' AS DiscountType

SET NOCOUNT OFF



-- ===============User Manager table=========================
CREATE TABLE [dbo].[UserManager](
	[UserId] [varchar](20) NOT NULL,
	[UserName] [varchar](50) NOT NULL,
	[JoiningDate] [datetime] NULL,
	[Designation] [varchar](100) NOT NULL,
	[Password] [varchar](20) NOT NULL,
	[grpAdd] [bit] NOT NULL,
	[grpSup] [bit] NOT NULL,
	[grpISup] [bit] NOT NULL,
	[grpUser] [bit] NOT NULL,
	[Active] [varchar](1) NOT NULL,
	[InvoiceFormat] [varchar](10) NOT NULL,
	[DefaultBusiness] [varchar](2) NOT NULL,
	[ManualDiscount] [varchar](1) NOT NULL,
	[FingerText] [varchar](1000) NOT NULL,
	[PasswordChangedDate] [datetime] NOT NULL,
	[HashPassword] [varchar](255) NOT NULL,
 CONSTRAINT [PK_UserManager] PRIMARY KEY NONCLUSTERED 
(
	[UserId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]


-- ======================Businness Config table============================
CREATE TABLE [dbo].[BusinessConfig](
	[ConfigID] [varchar](255) NULL,
	[HeaderTitle1] [varchar](255) NULL,
	[HeaderTitle2] [varchar](255) NULL,
	[HeaderTitle3] [varchar](255) NULL,
	[BrandTitle] [varchar](100) NULL,
	[CompanyName] [varchar](100) NULL,
	[ShopAddress] [varchar](255) NULL,
	[VatRegNo] [varchar](255) NULL,
	[FooterTitle1] [varchar](255) NULL,
	[FooterTitle2] [varchar](255) NULL,
	[FooterTitle3] [varchar](255) NULL,
	[FooterTitle4] [varchar](255) NULL,
	[FooterTitle5] [varchar](255) NULL,
	[FooterClosing] [varchar](255) NULL
) ON [PRIMARY]
GO


-- ======================SP_GetReturnInvoiceDetails Procedure============================

ALTER PROCEDURE [dbo].[SP_GetReturnInvoiceDetails]
    @InvoiceNo VARCHAR(100)
AS
/*
Declare @InvoiceNo AS VARCHAR(100)

SET @InvoiceNo = 'D0072401110001'

-- EXEC SP_GetReturnInvoiceDetails 'S0302402100009'
--*/
SET NOCOUNT ON 

SELECT P.ProductCode, P.ProductName, ID.VAT, ID.Discount, ID.LoyaltyDiscount, ID.VATDiscount ,I.DepotCode, CONVERT(NUMERIC(16,4),0) AS StockQuantity,  P.PackSize, ID.UnitPrice, ID.SalesQTY, ID.BonusQTY, ID.SalesTP,ID.TP,
	'' AS ReturnAmt, 0 AS ReturnQty, 'R' as InvoiceType, PP.VATDiscPerc, PP.VATPerc
INTO #InvDetails
FROM Product P
JOIN ProductPrice PP ON PP.ProductCode = P.ProductCode
JOIN InvoiceDetails ID ON ID.ProductCode = P.ProductCode
JOIN Invoice I ON I.InvoiceNo = ID.Invoiceno
WHERE I.InvoiceNo = @InvoiceNo;

update #InvDetails
	SET StockQuantity  = S.BatchQTY
from #InvDetails D 
INNER JOIN StockBatch S ON S.DepotCode = D.DepotCode AND S.ProductCode = D.ProductCode 
where D.SalesTP = D.UnitPrice

update #InvDetails
	SET StockQuantity  = S.StockQty
from #InvDetails D 
INNER JOIN StockClearance S ON S.DepotCode = D.DepotCode AND S.ProductCode = D.ProductCode AND D.SalesTP = S.UnitPrice 
where D.SalesTP <> D.UnitPrice

select * from #InvDetails
select * from Invoice where InvoiceNo = @InvoiceNo

DROP TABLE #InvDetails

SET NOCOUNT OFF
