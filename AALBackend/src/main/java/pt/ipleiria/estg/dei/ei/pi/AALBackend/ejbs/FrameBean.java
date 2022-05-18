package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;


import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Classification;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;

import javax.ejb.Stateless;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.*;
import javax.persistence.*;

@Stateless
public class FrameBean {
    @PersistenceContext
    private EntityManager entityManager;

    /**
     * Register a new frame from iteration
     * @param fileName
     * @param accuracy
     * @param filePath
     * @param iterationID
     * @param createDate
     * @param predictionsString “neutral#67;neutral#67;neutral#67;neutral#67”
     * @return
     * @throws Exception
     */
    public Long create(String fileName, Double accuracy, String filePath, Long iterationID, Date createDate, String predictionsString) throws Exception{
        if (fileName == null || fileName.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"File Name\" is required");
        if (filePath == null || filePath.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"File Path\" is required");
        Date now = Date.from(Instant.now().plus(1, ChronoUnit.HOURS));
        if(createDate == null || createDate.after(now)){
            throw new IllegalArgumentException("[Error] - Create Date is mandatory and must be before today's date");
        }
        if(accuracy == null || accuracy.isNaN() || accuracy < 0 || accuracy > 100){
            throw new IllegalArgumentException("[Error] - Accuracy is mandatory and should be a number between [0;100]");
        }
        Iteration iterationFound = entityManager.find(Iteration.class,iterationID);
        if(iterationFound == null){
            throw new MyEntityNotFoundException("[Error] - Iteration with email: \'"+iterationID+"\' not found");
        }

        Frame frame = new Frame(fileName, accuracy, filePath, iterationFound, createDate);
        iterationFound.addFrame(frame);

        for (String predictionString:predictionsString.split(";")) {
            Emotion emotion = findEmotion(predictionString.split("#")[0]);
            Classification prediction = new Classification(emotion,Double.parseDouble(predictionString.split("#")[1]), frame);
            frame.addPrediction(prediction);
            emotion.addClassification(prediction);
        }
        
        try {
            entityManager.persist(frame);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }

        return frame.getId();
    }

    /**
     * Finds Frame by given @Unique:id
     * @param id
     * @return Frame with the @Unique:id
     * @throws Exception
     */
    public Frame findFrame(Long id) throws Exception {
        Frame frame = entityManager.find(Frame.class, id);
        if(frame == null){
            throw new MyEntityNotFoundException("[Error] - Frame with id: \'"+id+"\' not Found");
        }
        return frame;
    }

    /**
     * Gets all the frames
     * @return
     */
    public List<Frame> getAllFrames() {
        return entityManager.createNamedQuery("getAllFrames", Frame.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Classify a Frame by given @Id:id
     * @param id
     * @return Frame classified
     * @throws Exception
     */
    public Frame classify(Long id, String emotionName) throws Exception{
        Frame frame = findFrame(id);

        Emotion emotion = findEmotion(emotionName);

        frame.setEmotion(emotion);
        emotion.addFrame(frame);
        entityManager.flush();
        return frame;
    }

    /**
     * Finds Emotion by given @Id:name
     * @param name
     * @return
     */
    public Emotion findEmotion(String name) throws Exception{
        Emotion emotion = entityManager.find(Emotion.class, name);
        if(emotion == null){
            throw new MyEntityNotFoundException("[Error] - Emotion with name: \'"+name+"\' not Found");
        }
        return emotion;
    }


    /**
     * Deletes a Frame by given @Id:id
     * @param id
     * @return
     * @throws Exception
     */
    public boolean delete(Long id) throws Exception{
        Frame frame = findFrame(id);
        entityManager.remove(frame);
        return entityManager.find(Frame.class, id) == null;
    }

    /**
     * Deletes a Frame
     * @param frame
     * @return
     * @throws Exception
     */
    public boolean delete(Frame frame) throws Exception{
        entityManager.remove(frame);
        return entityManager.find(Frame.class, frame) == null;
    }

    /***
     * get the frames by iteration
     * @param iteration
     * @return list of frames of a specific iteration
     */
    public List<Frame> getIterationFrames(Iteration iteration) {
        return iteration.getFrames();
    }

    /**
     * Get the frames by iteration @Id:id
     * @param iterationId @Id:id
     * @return list of frames of a specific iteration
     */
    public List<Frame> getIterationFrames(long iterationId) throws Exception{
        Iteration iteration = findIteration(iterationId);
        return iteration.getFrames();
    }

    /**
     * 
     * @param iterationId
     * @return
     */
    private Iteration findIteration(long iterationId) throws Exception{
        Iteration iteration = entityManager.find(Iteration.class, iterationId);
        if(iteration == null){
            throw new MyEntityNotFoundException("[Error] - Iteration with id: \'"+iterationId+"\' not Found");
        }
        return iteration;
    }

    /**
     * Queries the database for graph data of hte frames
     * @param id
     * @return
     * @throws MyEntityNotFoundException
     */
    public List<Frame> getGraphDataFrames(Long id) throws MyEntityNotFoundException {
        TypedQuery<Frame> query = entityManager.createQuery("SELECT f FROM Frame f JOIN Iteration i ON f.iteration.id = i.id JOIN Emotion e ON i.emotion.name = e.name WHERE i.client.id = "+id+" AND e.name <> 'invalid' ORDER BY f.createDate", Frame.class);
        return query.setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Returns the statistics for the classifications
     * @param pattern
     * @param id
     * @return
     * @throws Exception
     */
    public List<Object[]> getCountClassifiedFramesByDate(String pattern, Long id) throws Exception {
        String sql;
        if(id == null){
            sql = "SELECT count(*),to_char(updated_at,"+pattern+") from Frames where emotion_name IS NOT NULL group by to_char(updated_at,"+pattern+")";
        }else{
            sql = "SELECT count(*),to_char(f.updated_at,"+pattern+") from Frames f JOIN Iterations i ON f.iteration_id = i.id where i.client_id = "+id+" and f.emotion_name IS NOT NULL group by to_char(f.updated_at,"+pattern+")";
        }

        List<Object[]> data;
        try{
            Query query = entityManager.createNativeQuery(sql);
            data = query.getResultList();
        }catch (Exception e){
            throw new MyIllegalArgumentException("[Error] - "+e.getMessage());
        }
        return data;

    }

}
