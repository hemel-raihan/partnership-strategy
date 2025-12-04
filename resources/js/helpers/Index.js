const langType = 'Bn';
const BagKoiTextBn = 'ব্যাগ কই ?';
const BagKoiTextEn = 'Need a bag?';
const YesTextBn = 'দিচ্ছি';
const YesTextEn = 'Need a bag?';
const NoTextBn = 'লাগবে না';
const NoTextEn = 'Need a bag?';

export default function bagKoi(){
    if(langType == 'Bn'){
        return BagKoiTextBn
    }
    else{
        return BagKoiTextEn
    }
}
export default function yes(){
    if(langType == 'Bn'){
        return YesTextBn
    }
    else{
        return YesTextEn
    }
}
export default function no(){
    if(langType == 'Bn'){
        return NoTextBn
    }
    else{
        return NoTextEn
    }
}