<template>
  <div class="container">
    <div class="header">
        <h1>Partnership Strategy</h1>
        <p>Comprehensive Partnership Assessment Framework</p>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressWidth }"></div>
    </div>

    <div class="form-container">
      
      <!-- Step Indicator -->
      <div class="step-indicator">
        <div v-for="n in totalSteps"
             :key="n"
             class="step"
             :class="{
                active: currentStep === n,
                completed: currentStep > n
             }">
        </div>
      </div>


      <!-- ========================== -->
      <!-- SECTION 1 -->
      <!-- ========================== -->
      <div class="section" v-show="currentStep === 1">
        <div class="section-title">Partner Information</div>
        <div class="section-subtitle">Basic details about the partnership</div>

        <div class="form-grid">
          <div class="form-group">
            <label>Partner Name <span class="required">*</span></label>
                        <input v-model="form.partnerName" type="text" placeholder="e.g., Global Ventures Ltd." :class="{ 'input-error': validationErrors.partnerName }">
            <p v-if="validationErrors.partnerName" class="error-text">{{ validationErrors.partnerName }}</p>
          </div>

          <div class="form-group">
            <label>Country <span class="required">*</span></label>
            <select v-model="form.country" :class="{ 'input-error': validationErrors.country }">
              <option value="" disabled>Select Country</option>
              <option v-for="country in countryList" :key="country" :value="country">{{ country }}</option>
            </select>
            <p v-if="validationErrors.country" class="error-text">{{ validationErrors.country }}</p>
          </div>

        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>Address</label>
                        <textarea v-model="form.address" placeholder="Full street address, city, zip/postal code..."></textarea>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Contact Person Name</label>
                        <input v-model="form.contactName" type="text" placeholder="e.g., Jane Doe, CEO">
          </div>

          <div class="form-group">
            <label>Contact Number</label>
                        <input v-model="form.contactNumber" type="tel" placeholder="017xxxxxxxx">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Email Address</label>
                        <input v-model="form.email" type="email" placeholder="contact@gmail.com" :class="{ 'input-error': validationErrors.email }">
            <p v-if="validationErrors.email" class="error-text">{{ validationErrors.email }}</p>
          </div>

          <div class="form-group">
            <label>Web Address</label>
                        <input v-model="form.website" type="text" placeholder="https://www.partnerwebsite.com" :class="{ 'input-error': validationErrors.website }">
            <p v-if="validationErrors.website" class="error-text">{{ validationErrors.website }}</p>
          </div>
        </div>
      </div>


      <!-- ========================== -->
      <!-- SECTION 2 -->
      <!-- ========================== -->
      <div class="section" v-show="currentStep === 2">
        <div class="section-title">Partnership Type</div>
        <div class="section-subtitle">Select the primary partnership category</div>

        <div class="info-box">
          ℹ️ Choose the partnership type that best describes your relationship
        </div>

        <div class="radio-group">

          <div class="radio-option"
            v-for="option in partnershipOptions"
            :key="option.value"
            :class="{ selected: form.partnershipType === option.value }"
            @click="selectPartnership(option)">
            
                        <input type="radio" :value="option.value" v-model="form.partnershipType" hidden>
            <label>{{ option.label }}</label>
          </div>

        </div>
                <p v-if="validationErrors.partnershipType" class="error-text required-field-message" style="margin-top:15px;">{{ validationErrors.partnershipType }}</p>

        <div v-if="form.partnershipType === 'others'" style="margin-top:20px;">
          <div class="form-grid full">
            <div class="form-group">
              <label>Please specify <span class="required">*</span></label>
                            <input v-model="form.otherPartnershipType" type="text" :class="{ 'input-error': validationErrors.otherPartnershipType }">
              <p v-if="validationErrors.otherPartnershipType" class="error-text">{{ validationErrors.otherPartnershipType }}</p>
            </div>
          </div>
        </div>
      </div>


      <!-- ========================== -->
      <!-- SECTION 3 -->
      <!-- ========================== -->
      <div class="section" v-show="currentStep === 3">
        <div class="section-title">Business Details</div>

        <div class="form-grid">
          <div class="form-group">
            <label>ACI Business/Department <span class="required">*</span></label>
                        <select v-model="form.aciDepartment" required :class="{ 'input-error': validationErrors.aciDepartment }">
              <option value="" disabled>Select Department/Business</option>
              <option v-for="dept in aciDepartmentOptions" :key="dept" :value="dept">{{ dept }}</option>
            </select>
            <p v-if="validationErrors.aciDepartment" class="error-text">{{ validationErrors.aciDepartment }}</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Initial Agreement Date</label>
            <input v-model="form.initialDate" type="date">
          </div>

          <div class="form-group">
            <label>Initial Agreement Value</label>
            <input v-model="form.initialValue" type="text" placeholder="e.g., BDT 50,000">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Latest Agreement Date</label>
            <input v-model="form.latestDate" type="date">
          </div>

          <div class="form-group">
            <label>Latest Agreement Value</label>
            <input v-model="form.latestValue" type="text" placeholder="e.g., BDT 50,000">
          </div>
        </div>
      </div>


      <!-- ========================== -->
      <!-- SECTION 4 -->
      <!-- ========================== -->
      <div class="section" v-show="currentStep === 4">
        <div class="section-title">ACI Contact Information</div>

        <div class="form-grid">
          <div class="form-group">
            <label>Contact Person Name <span class="required">*</span></label>
            <input v-model="form.aciContactName" placeholder="Your Name" type="text" :class="{ 'input-error': validationErrors.aciContactName }">
            <p v-if="validationErrors.aciContactName" class="error-text">{{ validationErrors.aciContactName }}</p>
          </div>

          <div class="form-group">
              <label>Contact Person Mobile <span class="required">*</span></label>
              <input v-model="form.aciMobile" type="tel" :class="{ 'input-error': validationErrors.aciMobile }" placeholder="017xxxxxxxx"> 
              <p v-if="validationErrors.aciMobile" class="error-text">{{ validationErrors.aciMobile }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>Contact Person Email <span class="required">*</span></label>
            <input v-model="form.aciEmail" placeholder="your.email@aci.com" type="email" :class="{ 'input-error': validationErrors.aciEmail }">
            <p v-if="validationErrors.aciEmail" placeholder="your.email@aci.com" class="error-text">{{ validationErrors.aciEmail }}</p>
          </div>
        </div>
      </div>


      <!-- ========================== -->
      <!-- SECTION 5 -->
      <!-- ========================== -->
      <div class="section" v-show="currentStep === 5">
        <div class="section-title">Strategic Enquiry</div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHY: Which core needs does this partnership address? <span class="required">*</span></label>
             <textarea v-model="form.whyNeeds" placeholder="Describe the core customer/business needs..." :class="{ 'input-error': validationErrors.whyNeeds }"></textarea>
            <p v-if="validationErrors.whyNeeds" class="error-text">{{ validationErrors.whyNeeds }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHY: What is the primary objective of this engagement? <span class="required">*</span></label>
            <textarea v-model="form.whyObjective" placeholder="Explain the main goal of partnering..." :class="{ 'input-error': validationErrors.whyObjective }"></textarea>
            <p v-if="validationErrors.whyObjective" class="error-text">{{ validationErrors.whyObjective }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHAT: What unique capabilities do both parties bring? <span class="required">*</span></label>
            <textarea v-model="form.whatCapabilities" placeholder="Detail complementary strengths..." :class="{ 'input-error': validationErrors.whatCapabilities }"></textarea>
            <p v-if="validationErrors.whatCapabilities" class="error-text">{{ validationErrors.whatCapabilities }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHAT: What is the competitive advantage/sweet spot? <span class="required">*</span></label>
            <textarea v-model="form.whatAdvantage" placeholder="Describe the unique market position created..." :class="{ 'input-error': validationErrors.whatAdvantage }"></textarea>
            <p v-if="validationErrors.whatAdvantage" class="error-text">{{ validationErrors.whatAdvantage }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHAT: What are the key focus areas/critical asks? <span class="required">*</span></label>
            <textarea v-model="form.whatFocus" placeholder="List critical expectations from partner..." :class="{ 'input-error': validationErrors.whatFocus }"></textarea>
            <p v-if="validationErrors.whatFocus" class="error-text">{{ validationErrors.whatFocus }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>HOW: What are the governance and execution mechanisms?</label>
            <textarea v-model="form.howGovernance" placeholder="Describe how partnership is managed..." :class="{ 'input-error': validationErrors.howGovernance }"></textarea>
            <p v-if="validationErrors.howGovernance" class="error-text">{{ validationErrors.howGovernance }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>HOW: What are the critical joint actions/initiatives? <span class="required">*</span></label>
            <textarea v-model="form.howActions" placeholder="Detail key collaborative initiatives..." :class="{ 'input-error': validationErrors.howActions }"></textarea>
            <p v-if="validationErrors.howActions" class="error-text">{{ validationErrors.howActions }}</p>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>HOW: What is the financial performance to date? <span class="required">*</span></label>
            <textarea v-model="form.howFinance" placeholder="Summarize partnership financial results..." :class="{ 'input-error': validationErrors.howFinance }"></textarea>
            <p v-if="validationErrors.howFinance" class="error-text">{{ validationErrors.howFinance }}</p>
          </div>
        </div>
      </div>


      <!-- ========================== -->
      <!-- SECTION 6 -->
      <!-- ========================== -->
      <div class="section" v-show="currentStep === 6">
        <div class="section-title">Foresight & Future Strategy</div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHAT NEXT: Anticipated duration and evolution pathway?</label>
            <textarea v-model="form.futureEvolution" placeholder="Describe the expected timeline (e.g., 3-5 years) and how the partnership will mature over time."></textarea>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHAT NEXT: Potential risks and mitigation strategies?</label>
             <textarea v-model="form.futureRisks" placeholder="Identify potential internal (e.g., resource) or external (e.g., market) risks and the plans to address them."></textarea>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>WHAT NEXT: How to adapt to emerging market dynamics?</label>
            <textarea v-model="form.futureAdaptation" placeholder="Outline the flexibility needed to respond to technology changes or new competitor threats."></textarea>
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>Additional Comments or Critical Observations</label>
            <textarea v-model="form.additionalComments" placeholder="Include any final notes, concerns, or success factors not covered above."></textarea>
          </div>
        </div>
      </div>

<!-- SECTION 7 -->

      <div class="section" v-show="currentStep === 7">
        <div class="section-title">Submission Information</div>

        <div class="form-grid full">
          <div class="form-group">
            <label>Submitted By <span class="required">*</span></label>
                        <input v-model="form.submittedBy" type="text" placeholder="Your Full Name" :class="{ 'input-error': validationErrors.submittedBy }">
            <p v-if="validationErrors.submittedBy" class="error-text">{{ validationErrors.submittedBy }}</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Email Address <span class="required">*</span></label>
                        <input v-model="form.submitterEmail" type="email" placeholder="your.email@aci.com" :class="{ 'input-error': validationErrors.submitterEmail }">
            <p v-if="validationErrors.submitterEmail" class="error-text">{{ validationErrors.submitterEmail }}</p>
          </div>

          <div class="form-group">
            <label>Contact Number <span class="required">*</span></label>
                        <input v-model="form.submitterContact" type="tel" placeholder="017xxxxxxxx" :class="{ 'input-error': validationErrors.submitterContact }">
            <p v-if="validationErrors.submitterContact" class="error-text">{{ validationErrors.submitterContact }}</p>
          </div>
        </div>
      </div>

      <!-- FOOTER BUTTONS -->
      <div class="form-actions">
        <button class="btn-secondary"
                v-if="currentStep > 1"
                @click="prevStep">← Previous</button>

        <button class="btn-primary"
                v-if="currentStep < totalSteps"
                @click="nextStep">Next →</button>

        <button class="btn-primary"
                v-if="currentStep === totalSteps"
                @click="submitSurvey">✓ Submit</button>
      </div>
    </div>
  </div>
</template>


<script>
import axios from "axios";
import { Common } from "../../mixins/common";

export default {
  mixins: [Common],
  data() {
    return {
      currentStep: 1,
      totalSteps: 7,
      validationErrors: {},

      form: {
        partnerName: "",
        country: "",
        address: "",
        contactName: "",
        contactNumber: "",
        email: "",
        website: "",

        partnershipType: "",
        otherPartnershipType: "",

        aciDepartment: "",
        initialDate: "",
        initialValue: "",
        latestDate: "",
        latestValue: "",

        aciContactName: "",
        aciMobile: "",
        aciEmail: "",

        whyNeeds: "",
        whyObjective: "",
        whatCapabilities: "",
        whatAdvantage: "",
        whatFocus: "",
        howGovernance: "",
        howActions: "",
        howFinance: "",

        futureEvolution: '', 
        futureRisks: '',
        futureAdaptation: '',
        additionalComments: '',

        submittedBy: '',
        submitterEmail: '',
        submitterContact: '',
      },

      partnershipOptions: [
        { label: "JV/Equity Partner", value: "jv" },
        { label: "Strategic Partner", value: "strategic" },
        { label: "Supplier/Vendor", value: "supplier" },
        { label: "Channel Partner", value: "channel" },
        { label: "Service Partner", value: "service" },
        { label: "Others", value: "others" },
      ],

      countryList: [
        "Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burundi","Cabo Verde","Cambodia","Cameroon","Canada","Central African Republic","Chad","Chile","China","Colombia","Comoros","Congo, Democratic Republic of the","Congo, Republic of the","Costa Rica","Cote d'Ivoire","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada",
        "Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Korea, North","Korea, South","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","North Macedonia","Norway","Oman",
        "Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russia","Rwanda","Saint Kitts and Nevis","Saint Lucia","Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe","Other"
      ],

      aciDepartmentOptions: [
        "Premiaflex Plastics Limited",
        "ACI Animal Genetics",
        "Crop Care & Public Health",
        "Sulphur",
        "ACI Salt Limited",
        "ACI Consumer Electronics",
        "Consumer",
        "Paint",
        "Agri Science",
        "Fertilizer",
        "Aerosol",
        "M. Coil",
        "Godrej",
        "Animal Health",
        "Distribution",
        "Trading",
        "NG",
        "MIS",
        "Pharma Sample",
        "ACI Foods",
        "Pharma",
        "Test Project",
        "Agriculture Equipment",
        "Seeds",
        "ACI Trading",
        "Servier",
        "Corporate",
        "FINANCE DEPARTMENT",
        "Accessories"
     ],
    };
  },

  computed: {
    progressWidth() {
      return ((this.currentStep / this.totalSteps) * 100) + "%";
    }
  },

  methods: {
    selectPartnership(option) {
      this.form.partnershipType = option.value;
      // Clear validation error when an option is selected
      delete this.validationErrors.partnershipType; 
    },

    isValidEmail(email) {
      if (!email) return true; // Not required, so empty is valid
      const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
    },

    isValidUrl(url) {
      if (!url) return true; // Not required, so empty is valid
      const re = /^(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)$/;
      return re.test(String(url).toLowerCase());
    },

    isValidMobile(mobile) {
      if (!mobile) return true;
      const re = /^01\d{9}$/; 
      return re.test(String(mobile));
    },

    validateStep(step) {
      this.validationErrors = {};
      let isValid = true;

      if (step === 1) {
        if (!this.form.partnerName) {
          this.validationErrors.partnerName = "Partner Name is required.";
          isValid = false;
        }
        if (!this.form.country) {
          this.validationErrors.country = "Country is required.";
          isValid = false;
        }
        if (this.form.email && !this.isValidEmail(this.form.email)) {
          this.validationErrors.email = "Invalid email format.";
          isValid = false;
        }
        if (this.form.website && !this.isValidUrl(this.form.website)) {
          this.validationErrors.website = "Invalid website URL format.";
          isValid = false;
        }
      } else if (step === 2) {
        if (!this.form.partnershipType) {
          this.validationErrors.partnershipType = "Please select a partnership type.";
          isValid = false;
        }
        if (this.form.partnershipType === 'others' && !this.form.otherPartnershipType) {
          this.validationErrors.otherPartnershipType = "Please specify the partnership type.";
          isValid = false;
        }
      } else if (step === 3) {
        if (!this.form.aciDepartment) {
          this.validationErrors.aciDepartment = "ACI Business/Department is required.";
          isValid = false;
        }
      } else if (step === 4) {
        if (!this.form.aciContactName) {
          this.validationErrors.aciContactName = "Contact Name is required.";
          isValid = false;
        }
        if (!this.form.aciMobile) {
          this.validationErrors.aciMobile = "Mobile number is required.";
          isValid = false;
        }
        if (!this.form.aciEmail || !this.isValidEmail(this.form.aciEmail)) {
          this.validationErrors.aciEmail = "A valid Email is required.";
          isValid = false;
        }
      } else if (step === 5) {
        // Validate all 8 required fields in Section 5
        const requiredFields = [
          'whyNeeds', 'whyObjective', 'whatCapabilities', 'whatAdvantage',
          'whatFocus', 'howGovernance', 'howActions', 'howFinance'
        ];
        requiredFields.forEach(field => {
          if (!this.form[field]) {
            this.validationErrors[field] = "This field is required for Strategic Enquiry.";
            isValid = false;
          }
        });
      }
      else if (step === 7) {
        if (!this.form.submittedBy) {
          this.validationErrors.submittedBy = "Submitted By name is required.";
          isValid = false;
        }
        // Email validation for submitter
        if (!this.form.submitterEmail || !this.isValidEmail(this.form.submitterEmail)) {
          this.validationErrors.submitterEmail = "A valid Email Address is required.";
          isValid = false;
        }
        // Mobile validation for submitter
        if (!this.form.submitterContact || !this.isValidMobile(this.form.submitterContact)) {
          this.validationErrors.submitterContact = "A valid 11-digit Contact Number starting with '01' is required.";
          isValid = false;
        }
      }

      // Step 6 has no required fields, so it's always valid

      return isValid;
    },

    nextStep() {
      // MODIFICATION: The totalSteps property in your data must now be 7 (or more if you have a final review/submit step)
      if (this.validateStep(this.currentStep)) {
        if (this.currentStep < this.totalSteps) {
          this.currentStep++;
          window.scrollTo(0, 0);
        }
      } else {
//         alert("Please fill in all required fields and correct any validation errors before proceeding."); // Keep this commented or remove it to rely on inline error messages
      }
    },

    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
        window.scrollTo(0, 0);
      }
    },

    async submitSurvey() {
      if (!this.validateStep(this.currentStep)) { 
          window.scrollTo(0, 0); 
          return; 
      }
      try {
        const res = await axios.post("/partnership-strategy/api/save-survey", this.form);
        this.successNoti("Survey submitted successfully!");
        this.resetForm();

      } catch (err) {
        console.error(err);
        this.errorNoti("Something went wrong!");
      }
    },

    resetForm() {
      this.currentStep = 1;
      for (let key in this.form) {
        this.form[key] = "";
      }
    }
  }
};
</script>


<style>
* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #06b6d4;
            --accent-light: #0891b2;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border: #e5e7eb;
            --bg-light: #f9fafb;
            --bg-white: #ffffff;
        }

        /* :root {

          --primary: #0f172a;
          --secondary: #1e293b;
          --accent: #06b6d4;
          --accent-light: #0891b2;
          --success: #10b981;
          --warning: #f59e0b;
          --error: #ef4444;
          --text-primary: #1f2937;
          --text-secondary: #6b7280;
          --border: #cccccc;
          --bg-light: #f9fafb;
          --bg-white: #ffffff;
        } */

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-primary);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: slideDown 0.6s ease-out;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            font-weight: 300;
        }

        .progress-bar {
            height: 4px;
            background: var(--border);
            border-radius: 10px;
            margin-bottom: 40px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-light) 100%);
            border-radius: 10px;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-container {
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.1);
            padding: 40px;
            animation: slideUp 0.6s ease-out;
        }

        .section {
            animation: fadeIn 0.4s ease-out;
        }

        .section.active {
            display: block;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: var(--accent);
            border-radius: 2px;
        }

        .section-subtitle {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .required {
            color: var(--error);
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        input[type="tel"],
        select,
        textarea {
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--bg-white);
            color: var(--text-primary);
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .radio-option:hover {
            border-color: var(--accent);
            background: rgba(6, 182, 212, 0.03);
        }

        .radio-option input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .radio-option input[type="radio"]:checked + label {
            color: var(--accent);
            font-weight: 600;
        }

        .radio-option.selected {
            border-color: var(--accent);
            background: rgba(6, 182, 212, 0.05);
        }

        .info-box {
            background: rgba(6, 182, 212, 0.05);
            border-left: 4px solid var(--accent);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--border);
        }

        button {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-primary);
            border: 2px solid var(--border);
            flex: 1;
        }

        .btn-secondary:hover {
            background: var(--border);
            transform: translateY(-2px);
        }

        .btn-primary:disabled,
        .btn-secondary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .step.active {
            background: var(--accent);
        }

        .step.completed {
            background: var(--success);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 16px;
            max-width: 500px;
            text-align: center;
            animation: slideUp 0.3s ease-out;
        }

        .modal-content h2 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .modal-content p {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 1rem;
        }

        .success-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-container {
                padding: 25px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                flex: 1;
            }
        }

        .char-count {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .input-error {
            border-color: var(--error) !important;
        }

        .error-text {
            font-size: 0.85rem;
            color: var(--error);
            margin-top: 5px;
        }

        .required-field-message {
            font-weight: 500;
        }
</style>
