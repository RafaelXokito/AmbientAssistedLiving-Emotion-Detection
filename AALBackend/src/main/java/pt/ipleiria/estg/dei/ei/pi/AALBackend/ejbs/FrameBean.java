package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;


import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;

import javax.ejb.Stateless;
import java.util.List;
import javax.persistence.*;

@Stateless
public class FrameBean {
    @PersistenceContext
    private EntityManager entityManager;

    /***
     * Register a new frame from iteration
     * @param filePath
     * @return
     * @throws Exception
     */
    public Long create(String fileName, String filePath, Long iterationID) throws Exception{
        if (fileName == null || fileName.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"File Name\" is required");
        if (filePath == null || filePath.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"File Path\" is required");
        Iteration iterationFound = entityManager.find(Iteration.class,iterationID);
        if(iterationFound == null){
            throw new MyEntityNotFoundException("[Error] - Iteration with email: \'"+iterationID+"\' not found");
        }

        Frame frame = new Frame(fileName, filePath, iterationFound);
        iterationFound.addFrame(frame);
        
        try {
            entityManager.persist(frame);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        System.out.println("FrameBean"+iterationFound.getFrames().size());
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
     * @param id
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
     * @param id
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
}
