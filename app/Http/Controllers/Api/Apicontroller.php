<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;use App\Models\Schedule;
use App\Models\City;use Auth;use Hash;use App\Models\Chapter;
use Illuminate\Http\Request;use App\Models\SubjectCategory;
use App\Models\Category;use App\Models\Contact;use Session;
use Illuminate\Support\Facades\DB;use App\Models\Question;
use URL;use Validator;use App\Models\CommonQuestion;
use App\Models\Course;use App\Models\Teacher;use App\Models\Membership;
use App\Models\HomeContent;use App\Models\CourseLecture;use App\Models\CourseFeature;
use App\Models\User;use App\Models\SubscribedCourses;use App\Models\TeacherCourse;

// header('Access-Control-Allow-Origin: *');
// header('Content-Type:application/json');

class Apicontroller extends Controller
{
    public function updateProfile(Request $req)
    {
        $rules = [
          'userId' => 'required|min:1|numeric',
        ];
        $validator = validator()->make($req->all(),$rules);
        if(!$validator->fails()){
          $user = User::where('id',$req->userId)->first();
          if($user){
            $user->name = ($req->name) ? $req->name : '';
            $user->address = ($req->address) ? $req->address : '';
            $user->mobile = ($req->mobile) ? $req->mobile : '';
            if($req->hasFile('userImage')){
                $image = $req->file('userImage');
                // return errorResponse('Invalid User Id',$image);
                $random = date('Ymdhis').rand(0000,9999);
                $image->move('upload/profile/',$random.'.'.$image->getClientOriginalExtension());
                $imageurl = url('upload/profile/'.$random.'.'.$image->getClientOriginalExtension());
                // $buckturl ='https://'.env('AWS_BUCKET').'.s3.'.env('AWS_REGION').'.amazonaws.com/';
                // $filePath = 'ewards/'.$random.'.'.$banner->getClientOriginalExtension();
                // Storage::disk('s3')->put($filePath, file_get_contents($banner), 'public');
                // $imageurl = $buckturl.$filePath;
                $user->image = $imageurl;
            }
            $user->save();
            return sendResponse('Profile Updated Success',$user);
          }
          return errorResponse('Invalid User Id');
        }
        return errorResponse($validator->errors()->first());
    }

    public function getScheduledData(Request $req)
    {
        $rules = [
          'teacherId' => 'required|min:1|numeric',
        ];
        $validator = validator()->make($req->all(),$rules);
        if(!$validator->fails()){
            $schedule = Schedule::where('teacherId',$req->teacherId)->get();
            return sendResponse('Teacher Scheduled Data',$schedule);
        }
        return errorResponse($validator->errors()->first());
    }

    public function saveTeacherSchedule(Request $req)
    {
        $rules = [
            'teacherId' => 'required|min:1|numeric',
            'date' => 'required',
            'time' => 'required',
            'monday' => 'required',
            'tuesday' => 'required',
            'wednesday' => 'required',
            'thurusday' => 'required',
            'friday' => 'required',
            'saturday' => 'required',
            'sunday' => 'required',
        ];
        $validator = validator()->make($req->all(),$rules);
        if(!$validator->fails()){
            Schedule::where('teacherId',$req->teacherId)->delete();
            $date = explode('@rajeev@', $req->date);
            $time = explode('@rajeev@', $req->time);
            $monday = explode('@rajeev@', $req->monday);
            $tuesday = explode('@rajeev@', $req->tuesday);
            $wednesday = explode('@rajeev@', $req->wednesday);
            $thurusday = explode('@rajeev@', $req->thurusday);
            $friday = explode('@rajeev@', $req->friday);
            $saturday = explode('@rajeev@', $req->saturday);
            $sunday = explode('@rajeev@', $req->sunday);
            
            foreach($date as $key => $eventData){
                if($eventData != ''){
                    $newSchedule = new Schedule();
                    $newSchedule->teacherId = $req->teacherId;
                    $newSchedule->date = date('Y-m-d',strtotime($date[$key]));
                    $newSchedule->time = date('H:i',strtotime($time[$key]));
                    $newSchedule->mon = ($monday[$key] == 'true') ? 1 : 0;
                    $newSchedule->tue = ($tuesday[$key] == 'true') ? 1 : 0;
                    $newSchedule->wed = ($wednesday[$key] == 'true') ? 1 : 0;
                    $newSchedule->thu = ($thurusday[$key] == 'true') ? 1 : 0;
                    $newSchedule->fri = ($friday[$key] == 'true') ? 1 : 0;
                    $newSchedule->sat = ($saturday[$key] == 'true') ? 1 : 0;
                    $newSchedule->sun = ($sunday[$key] == 'true') ? 1 : 0;
                    $newSchedule->save();
                }
            }
            return sendResponse('Scheduled Data Saved Success');
        }
        return errorResponse($validator->errors()->first());
    }

    public function changeUserPassword(Request $req)
    {
        if(!empty($req->userId)){
          if(!empty($req->password) && !empty($req->confirmpassword)){
            if($req->password == $req->confirmpassword){
              $user = User::where('id',$req->userId)->first();
              if($user){
                $user->password = Hash::make($req->password);
                $user->save();
                return sendResponse('Password Updated Success');
              }
              return errorResponse('Invalid User id');
            }
            return errorResponse('password and confirm password should be same');
          }
          return errorResponse('password and confirm password is required');
        }
        return errorResponse('userId is required');
    }

    public function getUserSubscribedCourses(Request $req,$subscribtionId = 0)
    {
        if(!empty($req->userId)){
          $user = User::where('id',$req->userId)->first();
          if($user){
            $subscribedCourse = SubscribedCourses::select('subscribed_courses.*')
              ->where('subscribed_courses.user_id',$user->id)->with('courses')->with('features');
            if($subscribtionId > 0){
              $subscribedCourse = $subscribedCourse->where('subscribed_courses.id',$subscribtionId)->first();
            }else{
              $subscribedCourse = $subscribedCourse->get();
            }
            return sendResponse('Subscribed Courses List',$subscribedCourse);
          }
          return errorResponse('Invalid User Id');
        }
        return errorResponse('userId is required');
    }

    public function saveUserSubscribedCourses(Request $req)
    {
        if(!empty($req->userId) && !empty($req->courseId)){
          $user = User::where('id',$req->userId)->first();
          if($user){
            $course = Course::where('id',$req->courseId)->first();
            if($course){
                $checkSubscription = SubscribedCourses::where('user_id',$user->id)->where('course_id',$course->id)->first();
                if(!$checkSubscription){
                    $newSubscription = new SubscribedCourses();
                    $newSubscription->user_id = $user->id;
                    $newSubscription->course_id = $course->id;
                    $newSubscription->save();
                    return sendResponse('Course Subscribed Success',$newSubscription);
                }
                return errorResponse('This course is already subscribed by you');
            }
            return errorResponse('Invalid User Id');
          }
          return errorResponse('Invalid User Id');
        }
        return errorResponse('userId and courseId is required');
    }

    public function getHomeContent(Request $req)
    {
        $HomeContent = HomeContent::get();
        $data = [];
        foreach ($HomeContent as $content) {
            $data[$content->key][] = $content;
        }
        $data['category'] = Category::get();
        return sendResponse('Home Content',$data);
    }

    public function getSubjectCategory(Request $req)
    {
        $subjectCategory = SubjectCategory::select('*')->with('category');
        if(!empty($req->subjectCategoryId) && $req->subjectCategoryId > 0){
            $subjectCategory = $subjectCategory->where('id',$req->subjectCategoryId);
        }
        if(!empty($req->categoryId) && $req->categoryId > 0){
            $subjectCategory = $subjectCategory->where('categoryId',$req->categoryId);
        }
        $subjectCategory = $subjectCategory->get();
        return sendResponse('Subject category',$subjectCategory);
    }

    public function getChapter(Request $req)
    {
        $chapter = Chapter::select('*')->with('category')->with('subjectCategory')->with('subChapter');
        if(!empty($req->chapterId)){
          $chapter = $chapter->where('id',$req->chapterId);  
        }
        if(!empty($req->subjectCategoryId)){
          $chapter = $chapter->where('subjectCategoryId',$req->subjectCategoryId);  
        }
        $chapter = $chapter->get();
        return sendResponse('Chapter List',$chapter);
    }

    public function getQuestion(Request $req)
    {
        $question = Question::select('*')->with('chapter');
        if(!empty($req->subjectCategoryId)){
          $question = $question->where('subjectCategoryId',$req->subjectCategoryId);  
        }
        if(!empty($req->chapterId)){
          $question = $question->where('chapterId',$req->chapterId);
        }
        $question = $question->get();
        return sendResponse('Question List',$question);
    }

        public function get_teacher(Request $req,$teacherId = 0)
    {
        if($teacherId == 0){
          $teacher = Teacher::get();
        }else{
            $teacher = Teacher::where('id',$teacherId)->first();
            $teacher->teacherCourses = Course::get();
        }
        return sendResponse('Teacher List',$teacher);
    }

    public function get_course(Request $req,$courseId = 0)
    {
        if($courseId == 0){
            $course = Course::with('teacher')->get();
        }else{
            $course = Course::where('id',$courseId)->with('teacher')->first();
            $course->isUserSubscribed = false;
            if(!empty($req->userId) && $req->userId > 0){
              $checkSubscription = SubscribedCourses::where('user_id',$req->userId)->where('course_id',$course->id)->first();
              if($checkSubscription){
                $course->isUserSubscribed = true;
              }
            }
            $course->similarCourses = Course::where('id','!=',$courseId)->with('teacher')->get();
            $course->features = CourseFeature::where('course_id',$courseId)->get();
            $course->lectures = CourseLecture::where('course_id',$courseId)->get();
        }
        return sendResponse('Course List',$course);
        // return sendResponse('Course List',$req->all());
    }

    public function getMembership(Request $req)
    {
        $data['membership'] = Membership::where('is_active',1)->with('question')->get();
        $data['commonQuestion'] = CommonQuestion::where('membership_id',0)->get();
        return sendResponse('MemberShip List',$data);
    }

    public function contactUsFormSubmit(Request $req)
    {
        $rules = [
          'name' => 'required|string|max:200',
          'email' => 'required|email|string',
          'message' => 'required|string|max:255'
        ];
        $validator = validator()->make($req->all(),$rules);
        if(!$validator->fails()){
            $contact = new Contact();
            $contact->name = $req->name;
            $contact->email = $req->email;
            $contact->mobile = ($req->mobile) ? $req->mobile : '';
            $contact->message = $req->message;
            $contact->save();
            return sendResponse('Thankyou for contact with us we will contact you soon!');
        }
        return errorResponse($validator->errors()->first());
    }

  	public $successStatus = 200;
    public $errorStatus = 401;

  	public function city($stateid)
  	{
        $city = City::where('is_active',1)->where('state_id',$stateid)->get();
        return response()
            ->json(['message'=>'success','status'=>'1',"city"=>$city], $this->successStatus);
  	}

    public function checkcoupon($couponcode)
    {
        $code = $couponcode;
        $use_date = date("Y-m-d");
        $result = DB::select("select * from coupon_codes where coupon_code='$code'
            and (start_date<='$use_date' and end_date>='$use_date')");
        if (count($result) > 0) {
            $offer_type = $result[0]->offer_type;
            $offer_rate = $result[0]->offer_rate;
            return response()
          ->json(['message'=>'success','status'=>'1','offer_type'=>$offer_type,'offer_rate'=>$offer_rate], $this->successStatus);
        } else {
            return response()
          ->json(['message'=>'error','status'=>'0'], $this->successStatus);
        }
    }
}