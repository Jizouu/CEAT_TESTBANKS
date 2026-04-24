import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router';
import { Button } from './ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Header } from './shared/Header';
import { Plus, FileText, Trash2, Users, BarChart, Archive } from 'lucide-react';
import { toast } from 'sonner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';

interface Class {
  id: string;
  name: string;
  code: string;
  schedule: string;
}

interface Course {
  id: string;
  classId: string;
  courseName: string;
  description: string;
}

interface Question {
  id: string;
  courseId: string;
  question: string;
  options: string[];
  correctAnswer: number;
}

export function FacultyDashboard() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [classes, setClasses] = useState<Class[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [questions, setQuestions] = useState<Question[]>([]);
  const [selectedClassId, setSelectedClassId] = useState<string>('');
  const [selectedCourseId, setSelectedCourseId] = useState<string>('');

  const [newClass, setNewClass] = useState({ name: '', code: '', schedule: '' });
  const [newCourse, setNewCourse] = useState({ courseName: '', description: '' });
  const [newQuestion, setNewQuestion] = useState({
    question: '',
    option1: '',
    option2: '',
    option3: '',
    option4: '',
    correctAnswer: 0
  });

  const [classDialogOpen, setClassDialogOpen] = useState(false);
  const [courseDialogOpen, setCourseDialogOpen] = useState(false);
  const [questionDialogOpen, setQuestionDialogOpen] = useState(false);

  const handleCreateClass = () => {
    if (newClass.name && newClass.code) {
      const classData: Class = {
        id: Date.now().toString(),
        ...newClass
      };
      setClasses([...classes, classData]);
      toast.success('Class created successfully!');
      setNewClass({ name: '', code: '', schedule: '' });
      setClassDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: `created class ${newClass.name}`,
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const handleCreateCourse = () => {
    if (selectedClassId && newCourse.courseName) {
      const courseData: Course = {
        id: Date.now().toString(),
        classId: selectedClassId,
        ...newCourse
      };
      setCourses([...courses, courseData]);
      toast.success('Course added successfully!');
      setNewCourse({ courseName: '', description: '' });
      setCourseDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: `added course ${newCourse.courseName}`,
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const handleCreateQuestion = () => {
    if (selectedCourseId && newQuestion.question && newQuestion.option1) {
      const questionData: Question = {
        id: Date.now().toString(),
        courseId: selectedCourseId,
        question: newQuestion.question,
        options: [
          newQuestion.option1,
          newQuestion.option2,
          newQuestion.option3,
          newQuestion.option4
        ].filter(opt => opt.trim()),
        correctAnswer: newQuestion.correctAnswer
      };
      setQuestions([...questions, questionData]);
      toast.success('Question added successfully!');
      setNewQuestion({ question: '', option1: '', option2: '', option3: '', option4: '', correctAnswer: 0 });
      setQuestionDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: 'added test question',
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const deleteClass = (id: string) => {
    setClasses(classes.filter(c => c.id !== id));
    setCourses(courses.filter(c => c.classId !== id));
    toast.success('Class deleted');
  };

  const deleteCourse = (id: string) => {
    setCourses(courses.filter(c => c.id !== id));
    setQuestions(questions.filter(q => q.courseId !== id));
    toast.success('Course deleted');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Faculty" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid gap-4 md:grid-cols-4 mb-8">
          <Button onClick={() => navigate('/view-students?classId=1&className=All Classes')} className="h-16 bg-[#800000] hover:bg-[#600000]">
            <Users className="w-5 h-5 mr-2" />
            View Students
          </Button>
          <Button onClick={() => navigate('/view-student-results')} className="h-16 bg-[#FF8C00] hover:bg-[#E67E00]">
            <FileText className="w-5 h-5 mr-2" />
            Student Results
          </Button>
          <Button onClick={() => navigate('/archived-classes')} className="h-16 bg-[#800000] hover:bg-[#600000]">
            <Archive className="w-5 h-5 mr-2" />
            Archived Classes
          </Button>
          <Button onClick={() => navigate('/faculty-analytics')} className="h-16 bg-[#FF8C00] hover:bg-[#E67E00]">
            <BarChart className="w-5 h-5 mr-2" />
            Analytics
          </Button>
        </div>

        <Tabs defaultValue="classes" className="w-full">
          <TabsList className="mb-6">
            <TabsTrigger value="classes">Classes</TabsTrigger>
            <TabsTrigger value="courses">Courses</TabsTrigger>
            <TabsTrigger value="questions">Questions</TabsTrigger>
          </TabsList>

          <TabsContent value="classes">
            <div className="flex items-center justify-between mb-4">
              <h2>My Classes</h2>
              <Dialog open={classDialogOpen} onOpenChange={setClassDialogOpen}>
                <DialogTrigger asChild>
                  <Button className="bg-[#FF8C00] hover:bg-[#E67E00]">
                    <Plus className="w-4 h-4 mr-2" />
                    Create Class
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Create New Class</DialogTitle>
                    <DialogDescription>Add a new class for students to enroll</DialogDescription>
                  </DialogHeader>
                  <div className="space-y-4 py-4">
                    <div className="space-y-2">
                      <Label>Class Name</Label>
                      <Input
                        placeholder="Introduction to Programming"
                        value={newClass.name}
                        onChange={(e) => setNewClass({ ...newClass, name: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Class Code</Label>
                      <Input
                        placeholder="CS101-2026"
                        value={newClass.code}
                        onChange={(e) => setNewClass({ ...newClass, code: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Schedule</Label>
                      <Input
                        placeholder="MWF 9:00-10:00 AM"
                        value={newClass.schedule}
                        onChange={(e) => setNewClass({ ...newClass, schedule: e.target.value })}
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button onClick={handleCreateClass} className="bg-[#800000] hover:bg-[#600000]">
                      Create Class
                    </Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            </div>

            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {classes.map((cls) => (
                <Card key={cls.id}>
                  <CardHeader>
                    <CardTitle>{cls.name}</CardTitle>
                    <CardDescription>{cls.code}</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <p className="text-sm mb-4">Schedule: {cls.schedule}</p>
                    <div className="flex gap-2">
                      <Button
                        onClick={() => setSelectedClassId(cls.id)}
                        className="flex-1 bg-[#800000] hover:bg-[#600000]"
                      >
                        Manage
                      </Button>
                      <Button
                        onClick={() => deleteClass(cls.id)}
                        variant="outline"
                        size="icon"
                        className="text-destructive"
                      >
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="courses">
            <div className="flex items-center justify-between mb-4">
              <h2>Courses</h2>
              <Dialog open={courseDialogOpen} onOpenChange={setCourseDialogOpen}>
                <DialogTrigger asChild>
                  <Button className="bg-[#FF8C00] hover:bg-[#E67E00]" disabled={classes.length === 0}>
                    <Plus className="w-4 h-4 mr-2" />
                    Add Course
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Add Course</DialogTitle>
                    <DialogDescription>Add a course to a class</DialogDescription>
                  </DialogHeader>
                  <div className="space-y-4 py-4">
                    <div className="space-y-2">
                      <Label>Select Class</Label>
                      <select
                        className="w-full p-2 border rounded-md"
                        value={selectedClassId}
                        onChange={(e) => setSelectedClassId(e.target.value)}
                      >
                        <option value="">Choose a class</option>
                        {classes.map(cls => (
                          <option key={cls.id} value={cls.id}>{cls.name}</option>
                        ))}
                      </select>
                    </div>
                    <div className="space-y-2">
                      <Label>Course Name</Label>
                      <Input
                        placeholder="Midterm Examination"
                        value={newCourse.courseName}
                        onChange={(e) => setNewCourse({ ...newCourse, courseName: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Description</Label>
                      <Textarea
                        placeholder="Course description..."
                        value={newCourse.description}
                        onChange={(e) => setNewCourse({ ...newCourse, description: e.target.value })}
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button onClick={handleCreateCourse} className="bg-[#800000] hover:bg-[#600000]">
                      Add Course
                    </Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
              {courses.map((course) => (
                <Card key={course.id}>
                  <CardHeader>
                    <CardTitle>{course.courseName}</CardTitle>
                    <CardDescription>
                      {classes.find(c => c.id === course.classId)?.name}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <p className="text-sm mb-4">{course.description}</p>
                    <div className="flex gap-2">
                      <Button
                        onClick={() => setSelectedCourseId(course.id)}
                        className="flex-1 bg-[#800000] hover:bg-[#600000]"
                      >
                        <FileText className="w-4 h-4 mr-2" />
                        Questions ({questions.filter(q => q.courseId === course.id).length})
                      </Button>
                      <Button
                        onClick={() => deleteCourse(course.id)}
                        variant="outline"
                        size="icon"
                        className="text-destructive"
                      >
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="questions">
            <div className="flex items-center justify-between mb-4">
              <h2>Test Questions</h2>
              <Dialog open={questionDialogOpen} onOpenChange={setQuestionDialogOpen}>
                <DialogTrigger asChild>
                  <Button className="bg-[#FF8C00] hover:bg-[#E67E00]" disabled={courses.length === 0}>
                    <Plus className="w-4 h-4 mr-2" />
                    Add Question
                  </Button>
                </DialogTrigger>
                <DialogContent className="max-w-2xl">
                  <DialogHeader>
                    <DialogTitle>Add Test Question</DialogTitle>
                    <DialogDescription>Create a new question for your test</DialogDescription>
                  </DialogHeader>
                  <div className="space-y-4 py-4 max-h-[60vh] overflow-y-auto">
                    <div className="space-y-2">
                      <Label>Select Course</Label>
                      <select
                        className="w-full p-2 border rounded-md"
                        value={selectedCourseId}
                        onChange={(e) => setSelectedCourseId(e.target.value)}
                      >
                        <option value="">Choose a course</option>
                        {courses.map(course => (
                          <option key={course.id} value={course.id}>{course.courseName}</option>
                        ))}
                      </select>
                    </div>
                    <div className="space-y-2">
                      <Label>Question</Label>
                      <Textarea
                        placeholder="Enter your question here..."
                        value={newQuestion.question}
                        onChange={(e) => setNewQuestion({ ...newQuestion, question: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Option 1</Label>
                      <Input
                        value={newQuestion.option1}
                        onChange={(e) => setNewQuestion({ ...newQuestion, option1: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Option 2</Label>
                      <Input
                        value={newQuestion.option2}
                        onChange={(e) => setNewQuestion({ ...newQuestion, option2: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Option 3</Label>
                      <Input
                        value={newQuestion.option3}
                        onChange={(e) => setNewQuestion({ ...newQuestion, option3: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Option 4</Label>
                      <Input
                        value={newQuestion.option4}
                        onChange={(e) => setNewQuestion({ ...newQuestion, option4: e.target.value })}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Correct Answer</Label>
                      <select
                        className="w-full p-2 border rounded-md"
                        value={newQuestion.correctAnswer}
                        onChange={(e) => setNewQuestion({ ...newQuestion, correctAnswer: parseInt(e.target.value) })}
                      >
                        <option value={0}>Option 1</option>
                        <option value={1}>Option 2</option>
                        <option value={2}>Option 3</option>
                        <option value={3}>Option 4</option>
                      </select>
                    </div>
                  </div>
                  <DialogFooter>
                    <Button onClick={handleCreateQuestion} className="bg-[#800000] hover:bg-[#600000]">
                      Add Question
                    </Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            </div>

            <div className="space-y-4">
              {questions.map((q, index) => (
                <Card key={q.id}>
                  <CardHeader>
                    <CardTitle>Question {index + 1}</CardTitle>
                    <CardDescription>
                      {courses.find(c => c.id === q.courseId)?.courseName}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <p className="mb-4">{q.question}</p>
                    <div className="space-y-2">
                      {q.options.map((opt, idx) => (
                        <div
                          key={idx}
                          className={`p-2 rounded ${idx === q.correctAnswer ? 'bg-green-100 border border-green-500' : 'bg-gray-50'}`}
                        >
                          {idx + 1}. {opt}
                          {idx === q.correctAnswer && <span className="ml-2 text-green-600">(Correct)</span>}
                        </div>
                      ))}
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
}
